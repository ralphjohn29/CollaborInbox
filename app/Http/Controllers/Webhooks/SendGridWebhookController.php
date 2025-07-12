<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Jobs\ProcessIncomingEmail;
use App\Models\Email;
use App\Models\EmailAccount;
use App\Models\Workspace;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class SendGridWebhookController extends Controller
{
    /**
     * Handle incoming email from SendGrid
     */
    public function handleInboundEmail(Request $request)
    {
        try {
            // Log the incoming request for debugging
            Log::info('SendGrid Inbound Email Webhook', [
                'headers' => $request->headers->all(),
                'content' => $request->all()
            ]);

            // Parse the email data
            $emailData = $this->parseSendGridEmail($request);
            
            // Find the email account based on the To address
            $emailAccount = $this->findEmailAccount($emailData['to']);
            
            if (!$emailAccount) {
                Log::warning('No email account found for address: ' . $emailData['to']);
                return response()->json(['status' => 'ignored'], 200);
            }

            // Create the email record
            $email = $this->createEmailRecord($emailAccount, $emailData);
            
            // Process attachments if any
            if (!empty($emailData['attachments'])) {
                $this->processAttachments($email, $emailData['attachments']);
            }
            
            // Dispatch job for further processing (auto-responses, notifications, etc.)
            ProcessIncomingEmail::dispatch($email);
            
            return response()->json(['status' => 'success'], 200);
            
        } catch (\Exception $e) {
            Log::error('SendGrid webhook error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return 200 to prevent SendGrid from retrying
            return response()->json(['status' => 'error'], 200);
        }
    }
    
    /**
     * Parse SendGrid email format
     */
    private function parseSendGridEmail(Request $request)
    {
        $envelope = json_decode($request->input('envelope', '{}'), true);
        
        return [
            'message_id' => $request->input('headers', ''),
            'from' => $request->input('from'),
            'from_name' => $this->extractName($request->input('from')),
            'from_email' => $this->extractEmail($request->input('from')),
            'to' => $this->extractEmail($request->input('to')),
            'cc' => $this->parseEmailList($request->input('cc')),
            'subject' => $request->input('subject'),
            'body_text' => $request->input('text'),
            'body_html' => $request->input('html'),
            'spam_score' => $request->input('spam_score', 0),
            'attachments' => $this->parseAttachments($request),
            'headers' => $request->input('headers'),
            'raw_payload' => $request->all()
        ];
    }
    
    /**
     * Extract email address from string
     */
    private function extractEmail($emailString)
    {
        if (preg_match('/<(.+?)>/', $emailString, $matches)) {
            return $matches[1];
        }
        return filter_var($emailString, FILTER_VALIDATE_EMAIL) ? $emailString : null;
    }
    
    /**
     * Extract name from email string
     */
    private function extractName($emailString)
    {
        if (preg_match('/^(.+?)\s*</', $emailString, $matches)) {
            return trim($matches[1], ' "\'');
        }
        return null;
    }
    
    /**
     * Parse email list (for CC/BCC)
     */
    private function parseEmailList($emailString)
    {
        if (empty($emailString)) {
            return null;
        }
        
        $emails = [];
        $parts = explode(',', $emailString);
        
        foreach ($parts as $part) {
            $email = $this->extractEmail(trim($part));
            if ($email) {
                $emails[] = $email;
            }
        }
        
        return !empty($emails) ? $emails : null;
    }
    
    /**
     * Parse attachments from request
     */
    private function parseAttachments(Request $request)
    {
        $attachments = [];
        $attachmentCount = (int) $request->input('attachments', 0);
        
        for ($i = 1; $i <= $attachmentCount; $i++) {
            if ($request->hasFile("attachment{$i}")) {
                $file = $request->file("attachment{$i}");
                $attachments[] = [
                    'file' => $file,
                    'filename' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize()
                ];
            }
        }
        
        return $attachments;
    }
    
    /**
     * Find email account by email address
     */
    private function findEmailAccount($emailAddress)
    {
        return EmailAccount::where('email_address', $emailAddress)
            ->where('is_active', true)
            ->first();
    }
    
    /**
     * Create email record in database
     */
    private function createEmailRecord($emailAccount, $emailData)
    {
        // Get the workspace (using the first one for now)
        $workspace = Workspace::first();
        
        return Email::create([
            'workspace_id' => $workspace->id,
            'conversation_id' => null, // Will be set by conversation manager
            'message_id' => $emailData['message_id'] ?: '<' . Str::uuid() . '@sendgrid>',
            'from_email' => $emailData['from_email'],
            'from_name' => $emailData['from_name'],
            'to_email' => json_encode([$emailData['to']]),
            'cc_email' => $emailData['cc'] ? json_encode($emailData['cc']) : null,
            'subject' => $emailData['subject'],
            'body_html' => $emailData['body_html'],
            'body_text' => $emailData['body_text'],
            'headers' => json_encode($emailData['headers']),
            'spam_score' => $emailData['spam_score'],
            'direction' => 'inbound',
            'status' => 'pending',
            'has_attachments' => !empty($emailData['attachments']),
            'attachment_count' => count($emailData['attachments']),
            'received_at' => now(),
        ]);
    }
    
    /**
     * Process and store attachments
     */
    private function processAttachments($email, $attachments)
    {
        foreach ($attachments as $attachment) {
            $path = $attachment['file']->store(
                'email-attachments/' . $email->id,
                'local'
            );
            
            $email->attachments()->create([
                'filename' => $attachment['filename'],
                'mime_type' => $attachment['mime_type'],
                'size' => $attachment['size'],
                'storage_path' => $path
            ]);
        }
    }
}
