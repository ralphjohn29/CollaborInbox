<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Tenant\MailboxConfiguration;
use App\Services\ImapService;
use App\Services\EmailParserService;
use App\Services\AttachmentService;
use App\Services\TenantContext;
use App\Traits\MonitorsQueuePerformance;
use Exception;
use Illuminate\Support\Facades\Log;

class FetchEmailsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, MonitorsQueuePerformance;

    /**
     * The tenant ID this job is processing.
     *
     * @var int|string
     */
    protected $tenantId;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 300; // 5 minutes

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
    public $queue = 'emails';

    /**
     * Create a new job instance.
     *
     * @param int|string $tenantId
     * @return void
     */
    public function __construct($tenantId)
    {
        $this->tenantId = $tenantId;
    }

    /**
     * Execute the job.
     *
     * @param ImapService $imapService
     * @param EmailParserService $emailParserService
     * @param AttachmentService $attachmentService
     * @return void
     */
    public function handle(ImapService $imapService, EmailParserService $emailParserService, AttachmentService $attachmentService)
    {
        $this->recordStartTime();
        
        try {
            // Set tenant context for this job
            app(TenantContext::class)->setTenant($this->tenantId);
            
            Log::info("Processing email fetch job for tenant", ['tenant_id' => $this->tenantId]);
            
            // Get all mailbox configurations for this tenant
            $mailboxes = MailboxConfiguration::where('status', 'active')->get();
            
            if ($mailboxes->isEmpty()) {
                Log::info("No active mailbox configurations found for tenant", ['tenant_id' => $this->tenantId]);
                $this->recordSuccessfulExecution();
                return;
            }
            
            foreach ($mailboxes as $mailbox) {
                $this->processMailbox($mailbox, $imapService, $emailParserService, $attachmentService);
            }
            
            $this->recordSuccessfulExecution();
        } catch (Exception $e) {
            $this->recordFailedExecution($e);
            throw $e; // Re-throw to trigger Laravel's job failure handling
        }
    }
    
    /**
     * Process a single mailbox configuration.
     *
     * @param MailboxConfiguration $mailbox
     * @param ImapService $imapService
     * @param EmailParserService $emailParserService
     * @param AttachmentService $attachmentService
     * @return void
     */
    protected function processMailbox(MailboxConfiguration $mailbox, ImapService $imapService, EmailParserService $emailParserService, AttachmentService $attachmentService)
    {
        try {
            Log::info("Fetching emails for mailbox", [
                'mailbox_id' => $mailbox->id,
                'email' => $mailbox->email_address
            ]);
            
            // Determine what to fetch based on last sync time
            $criteria = $this->buildFetchCriteria($mailbox);
            
            // Fetch messages from the IMAP server
            $messages = $imapService->fetchMessages(
                $mailbox, 
                $mailbox->folder_to_monitor ?? 'INBOX',
                $criteria,
                false // Don't mark as read automatically
            );
            
            if (!$messages) {
                Log::warning("Failed to fetch messages or no messages available", [
                    'mailbox_id' => $mailbox->id,
                    'folder' => $mailbox->folder_to_monitor ?? 'INBOX'
                ]);
                return;
            }
            
            Log::info("Fetched messages", [
                'mailbox_id' => $mailbox->id,
                'count' => count($messages)
            ]);
            
            // Process each message
            foreach ($messages as $message) {
                try {
                    // Dispatch another job to process each email to prevent this job from timing out
                    ProcessEmailJob::dispatch(
                        $this->tenantId,
                        $mailbox->id,
                        $message->getMessageId(),
                        $emailParserService->parseMessage($message)
                    )->onQueue('email-processing');
                    
                } catch (Exception $e) {
                    Log::error("Error processing individual message", [
                        'mailbox_id' => $mailbox->id,
                        'message_id' => $message->getMessageId() ?? 'unknown',
                        'error' => $e->getMessage()
                    ]);
                    // Continue with next message
                }
            }
            
            // Update last_sync_timestamp
            $mailbox->last_sync_timestamp = now();
            $mailbox->last_error = null; // Clear any previous errors
            $mailbox->save();
            
        } catch (Exception $e) {
            Log::error("Error fetching emails for mailbox", [
                'mailbox_id' => $mailbox->id,
                'email' => $mailbox->email_address,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Record the error in the mailbox configuration
            $mailbox->last_error = $e->getMessage();
            $mailbox->save();
        }
    }
    
    /**
     * Build IMAP search criteria based on last sync time.
     *
     * @param MailboxConfiguration $mailbox
     * @return string
     */
    protected function buildFetchCriteria(MailboxConfiguration $mailbox): string
    {
        // If we have a last sync timestamp, only fetch newer messages
        if ($mailbox->last_sync_timestamp) {
            // Format date for IMAP search: 01-Jan-2023
            $date = $mailbox->last_sync_timestamp->format('d-M-Y');
            return "SINCE \"{$date}\" UNSEEN";
        }
        
        // For initial sync, limit to recent messages (last 7 days)
        $date = now()->subDays(7)->format('d-M-Y');
        return "SINCE \"{$date}\"";
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
        
        Log::error("FetchEmailsJob failed for tenant", [
            'tenant_id' => $this->tenantId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
} 