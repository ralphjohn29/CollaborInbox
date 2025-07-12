<?php

namespace App\Services\EmailProviders;

use App\Models\Email;
use App\Models\EmailAccount;
use App\Models\EmailAttachment;
use App\Models\Workspace;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImapEmailFetcher
{
    protected $connection;
    protected $emailAccount;
    protected $workspace;
    
    public function __construct(EmailAccount $emailAccount)
    {
        $this->emailAccount = $emailAccount;
        $this->workspace = Workspace::find(1); // Using first workspace for now
    }
    
    /**
     * Connect to IMAP server
     */
    public function connect()
    {
        $connectionString = sprintf(
            '{%s:%d/imap/%s%s}INBOX',
            $this->emailAccount->incoming_server_host,
            $this->emailAccount->incoming_server_port,
            $this->emailAccount->incoming_server_ssl ? 'ssl' : '',
            '/novalidate-cert' // For development - remove in production
        );
        
        $this->connection = @imap_open(
            $connectionString,
            $this->emailAccount->incoming_server_username,
            decrypt($this->emailAccount->incoming_server_password)
        );
        
        if (!$this->connection) {
            throw new \Exception('IMAP connection failed: ' . imap_last_error());
        }
        
        return $this;
    }
    
    /**
     * Fetch new emails
     */
    public function fetchNewEmails($limit = 50)
    {
        if (!$this->connection) {
            throw new \Exception('Not connected to IMAP server');
        }
        
        // Search for unseen emails
        $emails = imap_search($this->connection, 'UNSEEN');
        
        if (!$emails) {
            return 0;
        }
        
        $count = 0;
        $emails = array_slice($emails, 0, $limit);
        
        foreach ($emails as $emailNumber) {
            try {
                $this->processEmail($emailNumber);
                $count++;
            } catch (\Exception $e) {
                Log::error('Error processing email', [
                    'email_number' => $emailNumber,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return $count;
    }
    
    /**
     * Process a single email
     */
    protected function processEmail($emailNumber)
    {
        $header = imap_headerinfo($this->connection, $emailNumber);
        $structure = imap_fetchstructure($this->connection, $emailNumber);
        
        // Extract email data
        $messageId = $header->message_id ?? '<' . uniqid() . '@imap>';
        $subject = $this->decodeHeader($header->subject ?? 'No Subject');
        $fromAddress = $header->from[0]->mailbox . '@' . $header->from[0]->host;
        $fromName = $this->decodeHeader($header->from[0]->personal ?? '');
        $toAddresses = $this->extractAddresses($header->to ?? []);
        $ccAddresses = $this->extractAddresses($header->cc ?? []);
        
        // Check if email already exists
        if (Email::where('message_id', $messageId)->exists()) {
            return;
        }
        
        // Get email body
        $body = $this->getEmailBody($emailNumber, $structure);
        
        // Create email record
        $email = Email::create([
            'workspace_id' => $this->workspace->id,
            'conversation_id' => 1, // Will be properly assigned later
            'message_id' => $messageId,
            'from_email' => $fromAddress,
            'from_name' => $fromName,
            'to_email' => json_encode($toAddresses),
            'cc_email' => !empty($ccAddresses) ? json_encode($ccAddresses) : null,
            'subject' => $subject,
            'body_html' => $body['html'],
            'body_text' => $body['text'],
            'direction' => 'inbound',
            'status' => 'pending',
            'has_attachments' => $this->hasAttachments($structure),
            'received_at' => \Carbon\Carbon::createFromTimestamp($header->udate),
            'in_reply_to' => $header->in_reply_to ?? null,
            'references' => isset($header->references) ? json_encode(explode(' ', $header->references)) : null,
        ]);
        
        // Process attachments
        if ($this->hasAttachments($structure)) {
            $this->processAttachments($email, $emailNumber, $structure);
        }
        
        // Mark email as seen
        imap_setflag_full($this->connection, $emailNumber, "\\Seen");
        
        // Dispatch processing job
        \App\Jobs\ProcessIncomingEmail::dispatch($email);
    }
    
    /**
     * Get email body (both HTML and text)
     */
    protected function getEmailBody($emailNumber, $structure)
    {
        $body = ['html' => '', 'text' => ''];
        
        if ($structure->type == 0) { // Simple message
            $body['text'] = $this->getPartBody($emailNumber, 1, $structure);
            if ($structure->subtype == 'HTML') {
                $body['html'] = $body['text'];
                $body['text'] = strip_tags($body['text']);
            }
        } elseif ($structure->type == 1) { // Multipart
            foreach ($structure->parts as $partNumber => $part) {
                $partNumber = $partNumber + 1;
                
                if ($part->type == 0) { // Text
                    $content = $this->getPartBody($emailNumber, $partNumber, $part);
                    
                    if ($part->subtype == 'PLAIN') {
                        $body['text'] = $content;
                    } elseif ($part->subtype == 'HTML') {
                        $body['html'] = $content;
                    }
                } elseif ($part->type == 1) { // Nested multipart
                    $nestedBody = $this->processMultipart($emailNumber, $part, $partNumber);
                    $body['html'] = $body['html'] ?: $nestedBody['html'];
                    $body['text'] = $body['text'] ?: $nestedBody['text'];
                }
            }
        }
        
        // If no text version, create from HTML
        if (empty($body['text']) && !empty($body['html'])) {
            $body['text'] = strip_tags($body['html']);
        }
        
        // If no HTML version, create from text
        if (empty($body['html']) && !empty($body['text'])) {
            $body['html'] = nl2br(htmlspecialchars($body['text']));
        }
        
        return $body;
    }
    
    /**
     * Process multipart sections
     */
    protected function processMultipart($emailNumber, $structure, $prefix = '')
    {
        $body = ['html' => '', 'text' => ''];
        
        foreach ($structure->parts as $index => $part) {
            $partNumber = $prefix . '.' . ($index + 1);
            
            if ($part->type == 0) { // Text
                $content = $this->getPartBody($emailNumber, $partNumber, $part);
                
                if ($part->subtype == 'PLAIN') {
                    $body['text'] = $content;
                } elseif ($part->subtype == 'HTML') {
                    $body['html'] = $content;
                }
            }
        }
        
        return $body;
    }
    
    /**
     * Get body of a specific part
     */
    protected function getPartBody($emailNumber, $partNumber, $structure)
    {
        $data = imap_fetchbody($this->connection, $emailNumber, $partNumber);
        
        // Decode based on encoding
        switch ($structure->encoding) {
            case 3: // BASE64
                $data = base64_decode($data);
                break;
            case 4: // QUOTED-PRINTABLE
                $data = quoted_printable_decode($data);
                break;
        }
        
        // Convert charset if needed
        if (isset($structure->parameters)) {
            foreach ($structure->parameters as $param) {
                if (strtoupper($param->attribute) == 'CHARSET' && strtoupper($param->value) != 'UTF-8') {
                    $data = mb_convert_encoding($data, 'UTF-8', $param->value);
                    break;
                }
            }
        }
        
        return $data;
    }
    
    /**
     * Check if email has attachments
     */
    protected function hasAttachments($structure)
    {
        if ($structure->type == 1) { // Multipart
            foreach ($structure->parts as $part) {
                if ($this->isAttachment($part)) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Process email attachments
     */
    protected function processAttachments($email, $emailNumber, $structure)
    {
        if ($structure->type == 1) { // Multipart
            foreach ($structure->parts as $partNumber => $part) {
                $partNumber = $partNumber + 1;
                
                if ($this->isAttachment($part)) {
                    $this->saveAttachment($email, $emailNumber, $partNumber, $part);
                }
            }
        }
    }
    
    /**
     * Check if part is an attachment
     */
    protected function isAttachment($part)
    {
        $disposition = strtolower($part->disposition ?? '');
        
        return $disposition == 'attachment' || 
               $disposition == 'inline' ||
               (isset($part->ifdparameters) && $part->ifdparameters);
    }
    
    /**
     * Save attachment to storage
     */
    protected function saveAttachment($email, $emailNumber, $partNumber, $part)
    {
        $filename = $this->getAttachmentFilename($part);
        $data = imap_fetchbody($this->connection, $emailNumber, $partNumber);
        
        // Decode attachment
        if ($part->encoding == 3) { // BASE64
            $data = base64_decode($data);
        } elseif ($part->encoding == 4) { // QUOTED-PRINTABLE
            $data = quoted_printable_decode($data);
        }
        
        // Save to storage
        $path = 'email-attachments/' . $email->id . '/' . $filename;
        Storage::put($path, $data);
        
        // Create attachment record
        EmailAttachment::create([
            'email_id' => $email->id,
            'filename' => $filename,
            'mime_type' => $this->getMimeType($part),
            'size' => strlen($data),
            'storage_path' => $path
        ]);
    }
    
    /**
     * Get attachment filename
     */
    protected function getAttachmentFilename($part)
    {
        $filename = 'attachment';
        
        if ($part->ifdparameters) {
            foreach ($part->parameters as $param) {
                if (strtoupper($param->attribute) == 'NAME') {
                    $filename = $param->value;
                    break;
                }
            }
        }
        
        if ($part->ifdisposition && $part->disposition == 'attachment') {
            foreach ($part->dparameters as $param) {
                if (strtoupper($param->attribute) == 'FILENAME') {
                    $filename = $param->value;
                    break;
                }
            }
        }
        
        return $this->decodeHeader($filename);
    }
    
    /**
     * Get MIME type
     */
    protected function getMimeType($part)
    {
        $types = [
            0 => 'text',
            1 => 'multipart',
            2 => 'message',
            3 => 'application',
            4 => 'audio',
            5 => 'image',
            6 => 'video',
            7 => 'other'
        ];
        
        $type = $types[$part->type] ?? 'other';
        $subtype = strtolower($part->subtype ?? 'unknown');
        
        return $type . '/' . $subtype;
    }
    
    /**
     * Extract email addresses from header
     */
    protected function extractAddresses($addresses)
    {
        $result = [];
        
        foreach ($addresses as $address) {
            $result[] = $address->mailbox . '@' . $address->host;
        }
        
        return $result;
    }
    
    /**
     * Decode email header
     */
    protected function decodeHeader($header)
    {
        $decoded = imap_mime_header_decode($header);
        $result = '';
        
        foreach ($decoded as $element) {
            $charset = $element->charset == 'default' ? 'UTF-8' : $element->charset;
            $text = $element->text;
            
            if ($charset != 'UTF-8') {
                $text = mb_convert_encoding($text, 'UTF-8', $charset);
            }
            
            $result .= $text;
        }
        
        return $result;
    }
    
    /**
     * Disconnect from IMAP server
     */
    public function disconnect()
    {
        if ($this->connection) {
            imap_close($this->connection);
            $this->connection = null;
        }
    }
    
    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->disconnect();
    }
}
