<?php

namespace App\Services;

use App\Models\Email;
use App\Models\EmailAccount;
use App\Models\EmailAttachment;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MicrosoftGraphEmailFetcher
{
    protected $client;
    protected $emailAccount;
    protected $accessToken;

    public function __construct(EmailAccount $emailAccount)
    {
        $this->emailAccount = $emailAccount;
        $this->accessToken = $this->getAccessToken();
        
        $this->client = new Client([
            'base_uri' => 'https://graph.microsoft.com/v1.0/',
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Accept' => 'application/json',
            ],
        ]);
    }

    /**
     * Fetch emails from Microsoft Graph API
     *
     * @param int $limit
     * @return array
     */
    public function fetchEmails($limit = 50)
    {
        try {
            // Get the most recent email date for this account
            $mostRecentEmail = Email::where('email_account_id', $this->emailAccount->id)
                ->orderBy('received_at', 'desc')
                ->first();
            
            $query = [
                '$top' => $limit,
                '$orderby' => 'receivedDateTime desc',
                '$select' => 'id,subject,bodyPreview,body,from,toRecipients,ccRecipients,receivedDateTime,hasAttachments,isRead,importance,conversationId',
            ];
            
            // If we have existing emails, only fetch newer ones
            if ($mostRecentEmail) {
                // Subtract 1 minute to catch emails that might have the same timestamp
                $lastDate = $mostRecentEmail->received_at->subMinute()->toIso8601String();
                $query['$filter'] = "receivedDateTime ge {$lastDate}";
                
                Log::info("Fetching emails newer than or equal to {$lastDate}", [
                    'account' => $this->emailAccount->email_address,
                ]);
            }
            
            // Get messages from inbox
            $response = $this->client->get('me/mailFolders/inbox/messages', [
                'query' => $query,
            ]);

            $data = json_decode($response->getBody(), true);
            $emails = $data['value'] ?? [];
            
            Log::info("Microsoft Graph API response", [
                'account' => $this->emailAccount->email_address,
                'total_emails_returned' => count($emails),
                'has_more' => isset($data['@odata.nextLink']),
            ]);
            
            $fetchedCount = 0;
            $skippedCount = 0;
            foreach ($emails as $emailData) {
                if ($this->storeEmail($emailData)) {
                    $fetchedCount++;
                } else {
                    $skippedCount++;
                }
            }

            Log::info("Fetched {$fetchedCount} emails from Outlook for account {$this->emailAccount->email_address}");
            
            $message = "Fetched {$fetchedCount} new email" . ($fetchedCount !== 1 ? 's' : '');
            if ($skippedCount > 0) {
                $message .= " ({$skippedCount} already in database)";
            }
            
            return [
                'success' => true,
                'count' => $fetchedCount,
                'skipped' => $skippedCount,
                'total' => count($emails),
                'message' => $message,
            ];

        } catch (ClientException $e) {
            $response = $e->getResponse();
            $error = json_decode($response->getBody(), true);
            
            Log::error('Microsoft Graph API error', [
                'account' => $this->emailAccount->email_address,
                'error' => $error,
                'status' => $response->getStatusCode(),
            ]);

            // If token is expired, we might need to refresh it
            if ($response->getStatusCode() === 401) {
                return [
                    'success' => false,
                    'error' => 'Access token expired. Please reconnect your Outlook account.',
                ];
            }

            return [
                'success' => false,
                'error' => $error['error']['message'] ?? 'Failed to fetch emails',
            ];
        } catch (\Exception $e) {
            Log::error('Error fetching emails from Microsoft Graph', [
                'account' => $this->emailAccount->email_address,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Store email in database
     *
     * @param array $emailData
     * @return bool
     */
    protected function storeEmail(array $emailData)
    {
        try {
            // Check if email already exists
            $existingEmail = Email::where('message_id', $emailData['id'])
                ->where('email_account_id', $this->emailAccount->id)
                ->first();

            if ($existingEmail) {
                // Update read status if changed
                if ($existingEmail->status === 'unread' && $emailData['isRead']) {
                    $existingEmail->update([
                        'status' => 'read',
                        'read_at' => now(),
                    ]);
                }
                return false;
            }

            // Extract email addresses
            $fromEmail = $emailData['from']['emailAddress']['address'] ?? '';
            $fromName = $emailData['from']['emailAddress']['name'] ?? '';
            
            $toEmails = [];
            foreach ($emailData['toRecipients'] ?? [] as $recipient) {
                $toEmails[] = $recipient['emailAddress']['address'];
            }
            
            $ccEmails = [];
            foreach ($emailData['ccRecipients'] ?? [] as $recipient) {
                $ccEmails[] = [
                    'address' => $recipient['emailAddress']['address'],
                    'name' => $recipient['emailAddress']['name'] ?? ''
                ];
            }

            // Create email record
            $email = Email::create([
                'tenant_id' => $this->emailAccount->tenant_id,
                'workspace_id' => $this->emailAccount->workspace_id,
                'email_account_id' => $this->emailAccount->id,
                'message_id' => $emailData['id'],
                'thread_id' => $emailData['conversationId'] ?? null,
                'subject' => $emailData['subject'] ?? '(No Subject)',
                'from_email' => $fromEmail,
                'from_name' => $fromName,
                'to_email' => implode(',', $toEmails),
                'cc' => $ccEmails,
                'body_text' => strip_tags($emailData['bodyPreview'] ?? ''),
                'body_html' => $emailData['body']['content'] ?? '',
                'status' => $emailData['isRead'] ? 'read' : 'unread',
                'read_at' => $emailData['isRead'] ? now() : null,
                'received_at' => \Carbon\Carbon::parse($emailData['receivedDateTime']),
                'has_attachments' => $emailData['hasAttachments'] ?? false,
            ]);

            // Fetch attachments if any
            if ($emailData['hasAttachments']) {
                $this->fetchAttachments($email, $emailData['id']);
            }

            return true;

        } catch (\Exception $e) {
            Log::error('Error storing email', [
                'email_id' => $emailData['id'] ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Fetch and store email attachments
     *
     * @param Email $email
     * @param string $messageId
     */
    protected function fetchAttachments(Email $email, string $messageId)
    {
        try {
            $response = $this->client->get("me/messages/{$messageId}/attachments");
            $data = json_decode($response->getBody(), true);
            $attachments = $data['value'] ?? [];

            foreach ($attachments as $attachmentData) {
                // Skip inline attachments
                if ($attachmentData['isInline'] ?? false) {
                    continue;
                }

                $this->storeAttachment($email, $attachmentData);
            }
        } catch (\Exception $e) {
            Log::error('Error fetching attachments', [
                'email_id' => $email->id,
                'message_id' => $messageId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Store attachment
     *
     * @param Email $email
     * @param array $attachmentData
     */
    protected function storeAttachment(Email $email, array $attachmentData)
    {
        try {
            $filename = $attachmentData['name'] ?? 'attachment';
            $contentType = $attachmentData['contentType'] ?? 'application/octet-stream';
            $size = $attachmentData['size'] ?? 0;
            
            // Generate unique filename
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $storedFilename = Str::uuid() . '.' . $extension;
            $path = 'attachments/' . $storedFilename;

            // For large attachments, we might need to download separately
            // For now, we'll store the metadata
            EmailAttachment::create([
                'email_id' => $email->id,
                'filename' => $filename,
                'mime_type' => $contentType,
                'size' => $size,
                'storage_path' => $path,
                'outlook_attachment_id' => $attachmentData['id'],
            ]);

        } catch (\Exception $e) {
            Log::error('Error storing attachment', [
                'email_id' => $email->id,
                'attachment_name' => $filename ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Map Outlook importance to priority
     *
     * @param string $importance
     * @return string
     */
    protected function mapImportance(string $importance): string
    {
        return match (strtolower($importance)) {
            'high' => 'high',
            'low' => 'low',
            default => 'normal',
        };
    }

    /**
     * Mark email as read in Outlook
     *
     * @param string $messageId
     * @return bool
     */
    public function markAsRead(string $messageId): bool
    {
        try {
            $this->client->patch("me/messages/{$messageId}", [
                'json' => [
                    'isRead' => true,
                ],
            ]);
            return true;
        } catch (\Exception $e) {
            Log::error('Error marking email as read in Outlook', [
                'message_id' => $messageId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Delete email from Outlook
     *
     * @param string $messageId
     * @return bool
     */
    public function deleteEmail(string $messageId): bool
    {
        try {
            $this->client->delete("me/messages/{$messageId}");
            return true;
        } catch (\Exception $e) {
            Log::error('Error deleting email from Outlook', [
                'message_id' => $messageId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get access token, refreshing if necessary
     *
     * @return string|null
     */
    protected function getAccessToken()
    {
        // Check if token is expired
        if ($this->emailAccount->oauth_expires_at && $this->emailAccount->oauth_expires_at->isPast()) {
            // Token is expired, try to refresh
            if ($this->refreshAccessToken()) {
                return $this->emailAccount->fresh()->oauth_access_token;
            }
            return null;
        }

        return $this->emailAccount->oauth_access_token;
    }

    /**
     * Refresh the access token using the refresh token
     *
     * @return bool
     */
    protected function refreshAccessToken(): bool
    {
        if (!$this->emailAccount->oauth_refresh_token) {
            Log::error('No refresh token available for account', [
                'account' => $this->emailAccount->email_address,
            ]);
            return false;
        }

        try {
            $client = new Client();
            $response = $client->post('https://login.microsoftonline.com/common/oauth2/v2.0/token', [
                'form_params' => [
                    'client_id' => config('services.outlook.client_id'),
                    'client_secret' => config('services.outlook.client_secret'),
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $this->emailAccount->oauth_refresh_token,
                    'scope' => 'https://graph.microsoft.com/.default offline_access',
                ],
            ]);

            $data = json_decode($response->getBody(), true);

            // Update tokens in database
            $this->emailAccount->update([
                'oauth_access_token' => $data['access_token'],
                'oauth_refresh_token' => $data['refresh_token'] ?? $this->emailAccount->oauth_refresh_token,
                'oauth_expires_at' => now()->addSeconds($data['expires_in'] ?? 3600),
            ]);

            Log::info('Successfully refreshed OAuth token for account', [
                'account' => $this->emailAccount->email_address,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to refresh OAuth token', [
                'account' => $this->emailAccount->email_address,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
