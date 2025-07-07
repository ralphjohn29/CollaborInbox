<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Webklex\PHPIMAP\Message;

class EmailParserService
{
    /**
     * The attachment service instance.
     * 
     * @var \App\Services\AttachmentService
     */
    protected $attachmentService;
    
    /**
     * Create a new EmailParserService instance.
     * 
     * @param \App\Services\AttachmentService $attachmentService
     * @return void
     */
    public function __construct(AttachmentService $attachmentService = null)
    {
        $this->attachmentService = $attachmentService ?: app(AttachmentService::class);
    }
    
    /**
     * Parse an IMAP message and extract all relevant metadata
     *
     * @param Message $message The IMAP message to parse
     * @return array The parsed email data
     */
    public function parseMessage(Message $message): array
    {
        try {
            // Extract basic header information
            $parsedEmail = [
                'message_id' => $this->extractMessageId($message),
                'references' => $this->extractReferences($message),
                'in_reply_to' => $this->extractInReplyTo($message),
                'subject' => $message->getSubject(),
                'date' => $message->getDate()->toDateTime(),
                'from' => $this->formatAddresses($message->getFrom()),
                'to' => $this->formatAddresses($message->getTo()),
                'cc' => $this->formatAddresses($message->getCc()),
                'bcc' => $this->formatAddresses($message->getBcc()),
                'has_attachments' => $message->hasAttachments(),
                'size' => $message->getSize(),
                'importance' => $this->getImportance($message),
                'is_flagged' => $message->isFlagged(),
                'is_answered' => $message->isAnswered(),
                'is_deleted' => $message->isDeleted(),
                'is_draft' => $message->isDraft(),
                'is_seen' => $message->isSeen(),
            ];

            // Extract and process the email body
            $parsedEmail['bodies'] = $this->extractBodies($message);
            
            // Determine the best body to display based on content type
            $parsedEmail['body_html'] = $parsedEmail['bodies']['html'] ?? null;
            $parsedEmail['body_plain'] = $parsedEmail['bodies']['plain'] ?? null;
            
            // Process attachments if any
            if ($message->hasAttachments()) {
                $parsedEmail['attachments'] = $this->processAttachments($message);
            } else {
                $parsedEmail['attachments'] = [];
            }
            
            return $parsedEmail;
        } catch (\Exception $e) {
            Log::error('Error parsing email', [
                'error' => $e->getMessage(),
                'message_id' => $message->getMessageId() ?? 'unknown'
            ]);
            
            // Return minimal data even if parsing fails
            return [
                'message_id' => $message->getMessageId() ?? 'error-' . uniqid(),
                'subject' => $message->getSubject() ?? 'Error parsing email',
                'date' => $message->getDate()->toDateTime() ?? now(),
                'from' => $this->formatAddresses($message->getFrom()),
                'parse_error' => $e->getMessage(),
                'attachments' => [],
            ];
        }
    }

    /**
     * Process attachments in the email
     *
     * @param Message $message
     * @return array
     */
    protected function processAttachments(Message $message): array
    {
        if (!$message->hasAttachments()) {
            return [];
        }
        
        try {
            $emailId = $this->extractMessageId($message);
            // Clean the email ID for use as a directory name
            $emailId = str_replace(['<', '>', '@'], ['', '', '_'], $emailId);
            
            return $this->attachmentService->processAttachments($message, $emailId);
        } catch (\Exception $e) {
            Log::error('Error processing attachments', [
                'error' => $e->getMessage(),
                'message_id' => $message->getMessageId() ?? 'unknown'
            ]);
            
            return [];
        }
    }

    /**
     * Extract the Message-ID header
     *
     * @param Message $message
     * @return string|null
     */
    protected function extractMessageId(Message $message): ?string
    {
        $messageId = $message->getMessageId();
        
        // Clean up the message ID if needed (some emails have additional characters)
        if ($messageId) {
            $messageId = trim($messageId, '<>');
            return '<' . $messageId . '>';
        }
        
        return null;
    }

    /**
     * Extract References header which contains IDs of previous messages in the thread
     *
     * @param Message $message
     * @return array
     */
    protected function extractReferences(Message $message): array
    {
        $references = [];
        
        // Get References header
        $referencesHeader = $message->getHeader('references');
        if (!empty($referencesHeader)) {
            // Split references and clean them up
            $refIds = preg_split('/\s+/', $referencesHeader);
            foreach ($refIds as $refId) {
                $refId = trim($refId);
                if (!empty($refId)) {
                    $refId = trim($refId, '<>');
                    $references[] = '<' . $refId . '>';
                }
            }
        }
        
        return $references;
    }

    /**
     * Extract In-Reply-To header which contains the ID of the message being replied to
     *
     * @param Message $message
     * @return string|null
     */
    protected function extractInReplyTo(Message $message): ?string
    {
        $inReplyTo = $message->getHeader('in-reply-to');
        
        if (!empty($inReplyTo)) {
            $inReplyTo = trim($inReplyTo, '<>');
            return '<' . $inReplyTo . '>';
        }
        
        return null;
    }

    /**
     * Format email addresses into a consistent structure
     *
     * @param array|null $addresses
     * @return array
     */
    protected function formatAddresses(?array $addresses): array
    {
        $result = [];
        
        if (empty($addresses)) {
            return $result;
        }
        
        foreach ($addresses as $address) {
            $result[] = [
                'email' => $address->mail,
                'name' => $address->personal ?? null,
                'full' => $address->full
            ];
        }
        
        return $result;
    }

    /**
     * Get message importance/priority from headers
     *
     * @param Message $message
     * @return string normal|high|low
     */
    protected function getImportance(Message $message): string
    {
        $importance = strtolower($message->getHeader('importance') ?? '');
        $priority = strtolower($message->getHeader('x-priority') ?? '');
        
        if (in_array($importance, ['high', 'highest']) || in_array($priority, ['1', '2'])) {
            return 'high';
        } elseif (in_array($importance, ['low', 'lowest']) || in_array($priority, ['4', '5'])) {
            return 'low';
        }
        
        return 'normal';
    }

    /**
     * Extract and decode all available body parts (HTML and plain text)
     *
     * @param Message $message
     * @return array
     */
    protected function extractBodies(Message $message): array
    {
        $bodies = [];
        
        // Try to get HTML body
        $htmlBody = $message->getHTMLBody();
        if (!empty($htmlBody)) {
            $bodies['html'] = $this->sanitizeHtml($htmlBody);
        }
        
        // Try to get plain text body
        $textBody = $message->getTextBody();
        if (!empty($textBody)) {
            $bodies['plain'] = $this->sanitizeText($textBody);
        }
        
        // If no bodies were found, try parsing the body directly
        if (empty($bodies)) {
            $body = $message->getBody();
            if (!empty($body)) {
                // Determine if it's HTML or plain text
                if (strpos($body, '<html') !== false || strpos($body, '<body') !== false) {
                    $bodies['html'] = $this->sanitizeHtml($body);
                } else {
                    $bodies['plain'] = $this->sanitizeText($body);
                }
            }
        }
        
        return $bodies;
    }

    /**
     * Sanitize HTML content to prevent XSS attacks
     *
     * @param string $html
     * @return string
     */
    protected function sanitizeHtml(string $html): string
    {
        // Basic sanitization - in a production environment, consider using
        // a proper HTML purifier library like HTMLPurifier
        
        // Convert encoding if needed
        $encoding = mb_detect_encoding($html, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
        if ($encoding && $encoding !== 'UTF-8') {
            $html = mb_convert_encoding($html, 'UTF-8', $encoding);
        }
        
        return $html;
    }

    /**
     * Sanitize plain text content
     *
     * @param string $text
     * @return string
     */
    protected function sanitizeText(string $text): string
    {
        // Convert encoding if needed
        $encoding = mb_detect_encoding($text, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
        if ($encoding && $encoding !== 'UTF-8') {
            $text = mb_convert_encoding($text, 'UTF-8', $encoding);
        }
        
        return $text;
    }

    /**
     * Create a standardized parsed email DTO from raw message
     *
     * @param Message $message
     * @return \stdClass
     */
    public function createEmailDTO(Message $message): \stdClass
    {
        $parsedData = $this->parseMessage($message);
        
        // Create a standardized object for use throughout the application
        $emailDTO = new \stdClass();
        $emailDTO->messageId = $parsedData['message_id'];
        $emailDTO->subject = $parsedData['subject'];
        $emailDTO->date = $parsedData['date'];
        $emailDTO->from = $parsedData['from'];
        $emailDTO->to = $parsedData['to'];
        $emailDTO->cc = $parsedData['cc'];
        $emailDTO->bodyHtml = $parsedData['body_html'];
        $emailDTO->bodyPlain = $parsedData['body_plain'];
        $emailDTO->hasAttachments = $parsedData['has_attachments'];
        $emailDTO->attachments = $parsedData['attachments'] ?? [];
        $emailDTO->inReplyTo = $parsedData['in_reply_to'];
        $emailDTO->references = $parsedData['references'];
        $emailDTO->importance = $parsedData['importance'];
        $emailDTO->isFlagged = $parsedData['is_flagged'];
        $emailDTO->isRead = $parsedData['is_seen'];
        
        return $emailDTO;
    }
} 