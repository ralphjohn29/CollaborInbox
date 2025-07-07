<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Thread;
use App\Models\Message;
use App\Models\Tenant\MailboxConfiguration;
use App\Services\TenantContext;
use App\Traits\MonitorsQueuePerformance;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, MonitorsQueuePerformance;

    /**
     * The tenant ID this job is processing.
     *
     * @var int|string
     */
    protected $tenantId;
    
    /**
     * The mailbox configuration ID.
     *
     * @var int
     */
    protected $mailboxId;
    
    /**
     * The email message ID from IMAP.
     *
     * @var string
     */
    protected $messageId;
    
    /**
     * The parsed email data.
     *
     * @var array
     */
    protected $emailData;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;
    
    /**
     * The queue this job should be sent to.
     *
     * @var string
     */
    public $queue = 'email-processing';

    /**
     * Create a new job instance.
     *
     * @param int|string $tenantId
     * @param int $mailboxId
     * @param string $messageId
     * @param array $emailData
     * @return void
     */
    public function __construct($tenantId, $mailboxId, $messageId, array $emailData)
    {
        $this->tenantId = $tenantId;
        $this->mailboxId = $mailboxId;
        $this->messageId = $messageId;
        $this->emailData = $emailData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->recordStartTime();
        
        try {
            // Set tenant context for this job
            app(TenantContext::class)->setTenant($this->tenantId);
            
            Log::info("Processing email", [
                'tenant_id' => $this->tenantId,
                'mailbox_id' => $this->mailboxId,
                'message_id' => $this->messageId
            ]);
            
            // Check if the message already exists to avoid duplicates
            if ($this->messageExists()) {
                Log::info("Message already exists, skipping", [
                    'message_id' => $this->messageId
                ]);
                $this->recordSuccessfulExecution();
                return;
            }
            
            DB::beginTransaction();
            
            try {
                // Determine if this is a new thread or part of an existing thread
                $thread = $this->findOrCreateThread();
                
                // Create the message record
                $message = $this->createMessage($thread);
                
                // Process attachments (if any)
                if (!empty($this->emailData['attachments'])) {
                    $this->processAttachments($message);
                }
                
                DB::commit();
                
                Log::info("Email successfully processed", [
                    'message_id' => $this->messageId,
                    'thread_id' => $thread->id
                ]);
                
                $this->recordSuccessfulExecution();
                
            } catch (Exception $e) {
                DB::rollBack();
                
                Log::error("Failed to process email", [
                    'message_id' => $this->messageId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                $this->recordFailedExecution($e);
                throw $e; // Rethrow to trigger job failure
            }
        } catch (Exception $e) {
            $this->recordFailedExecution($e);
            throw $e; // Rethrow to trigger job failure
        }
    }
    
    /**
     * Check if the message already exists in the database.
     *
     * @return bool
     */
    protected function messageExists(): bool
    {
        return Message::where('message_id', $this->messageId)->exists();
    }
    
    /**
     * Find an existing thread based on references or create a new one.
     *
     * @return Thread
     */
    protected function findOrCreateThread(): Thread
    {
        // Check if this message is a reply to an existing thread
        if (!empty($this->emailData['in_reply_to'])) {
            $existingMessage = Message::where('message_id', $this->emailData['in_reply_to'])->first();
            
            if ($existingMessage) {
                return $existingMessage->thread;
            }
        }
        
        // Check references for thread matching
        if (!empty($this->emailData['references'])) {
            foreach ($this->emailData['references'] as $reference) {
                $existingMessage = Message::where('message_id', $reference)->first();
                
                if ($existingMessage) {
                    return $existingMessage->thread;
                }
            }
        }
        
        // No existing thread found, create a new one
        $thread = new Thread();
        $thread->subject = $this->emailData['subject'] ?? 'No Subject';
        
        // If we have sender information, use it for the thread's origin
        if (!empty($this->emailData['from'][0]['email'])) {
            $thread->origin_email = $this->emailData['from'][0]['email'];
            $thread->origin_name = $this->emailData['from'][0]['name'] ?? null;
        }
        
        $thread->mailbox_id = $this->mailboxId;
        $thread->status = 'new'; // Default status for new threads
        $thread->save();
        
        return $thread;
    }
    
    /**
     * Create a new message record.
     *
     * @param Thread $thread
     * @return Message
     */
    protected function createMessage(Thread $thread): Message
    {
        $message = new Message();
        $message->thread_id = $thread->id;
        $message->message_id = $this->messageId;
        $message->in_reply_to = $this->emailData['in_reply_to'] ?? null;
        
        // Set references as JSON if available
        if (!empty($this->emailData['references'])) {
            $message->references = json_encode($this->emailData['references']);
        }
        
        // Sender information
        if (!empty($this->emailData['from'][0])) {
            $message->from_email = $this->emailData['from'][0]['email'] ?? null;
            $message->from_name = $this->emailData['from'][0]['name'] ?? null;
        }
        
        // Store recipients
        if (!empty($this->emailData['to'])) {
            $message->to = json_encode($this->emailData['to']);
        }
        
        if (!empty($this->emailData['cc'])) {
            $message->cc = json_encode($this->emailData['cc']);
        }
        
        if (!empty($this->emailData['bcc'])) {
            $message->bcc = json_encode($this->emailData['bcc']);
        }
        
        // Content
        $message->subject = $this->emailData['subject'] ?? 'No Subject';
        $message->body_html = $this->emailData['body_html'] ?? null;
        $message->body_plain = $this->emailData['body_plain'] ?? null;
        
        // Set received date from the email
        if (!empty($this->emailData['date'])) {
            $message->received_at = $this->emailData['date'];
        } else {
            $message->received_at = now();
        }
        
        $message->is_incoming = true; // This is an incoming message
        $message->mailbox_id = $this->mailboxId;
        
        $message->save();
        
        return $message;
    }
    
    /**
     * Process attachments for the message.
     *
     * @param Message $message
     * @return void
     */
    protected function processAttachments(Message $message): void
    {
        foreach ($this->emailData['attachments'] as $attachmentData) {
            $attachment = new \App\Models\Attachment();
            $attachment->message_id = $message->id;
            $attachment->thread_id = $message->thread_id;
            $attachment->original_filename = $attachmentData['original_filename'];
            $attachment->stored_filename = $attachmentData['stored_filename'];
            $attachment->path = $attachmentData['path'];
            $attachment->content_type = $attachmentData['content_type'];
            $attachment->size = $attachmentData['size'];
            $attachment->is_inline = $attachmentData['is_inline'];
            $attachment->content_id = $attachmentData['content_id'] ?? null;
            $attachment->save();
        }
    }
    
    /**
     * Handle a job failure.
     *
     * @param Exception $exception
     * @return void
     */
    public function failed(Exception $exception)
    {
        $this->recordFailedExecution($exception);
        
        Log::error("ProcessEmailJob failed", [
            'tenant_id' => $this->tenantId,
            'mailbox_id' => $this->mailboxId,
            'message_id' => $this->messageId,
            'error' => $exception->getMessage()
        ]);
    }
} 