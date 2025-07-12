<?php

namespace App\Jobs;

use App\Models\Email;
use App\Models\Conversation;
use App\Services\ConversationManager;
use App\Services\AutoResponder;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessIncomingEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $email;

    /**
     * Create a new job instance.
     */
    public function __construct(Email $email)
    {
        $this->email = $email;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            // 1. Assign to conversation (thread management)
            $this->assignToConversation();
            
            // 2. Apply automation rules
            $this->applyAutomationRules();
            
            // 3. Check for auto-responses
            $this->checkAutoResponse();
            
            // 4. Send notifications
            $this->sendNotifications();
            
            // 5. Update email status
            $this->email->update(['status' => 'unread']);
            
            Log::info('Processed incoming email', [
                'email_id' => $this->email->id,
                'from' => $this->email->from_email,
                'subject' => $this->email->subject
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error processing incoming email', [
                'email_id' => $this->email->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e; // Re-throw to mark job as failed
        }
    }
    
    /**
     * Assign email to a conversation (thread)
     */
    private function assignToConversation()
    {
        // Check if this is a reply to an existing conversation
        if ($this->email->in_reply_to || $this->email->references) {
            $existingConversation = $this->findExistingConversation();
            if ($existingConversation) {
                $this->email->update(['conversation_id' => $existingConversation->id]);
                $existingConversation->increment('email_count');
                $existingConversation->update(['last_response_at' => now()]);
                return;
            }
        }
        
        // Create new conversation
        $conversation = Conversation::create([
            'workspace_id' => $this->email->workspace_id,
            'uid' => $this->generateConversationUid(),
            'subject' => $this->email->subject,
            'status' => 'new',
            'disposition' => 'new',
            'customer_email' => $this->email->from_email,
            'customer_name' => $this->email->from_name,
            'email_count' => 1,
        ]);
        
        $this->email->update(['conversation_id' => $conversation->id]);
    }
    
    /**
     * Find existing conversation based on references
     */
    private function findExistingConversation()
    {
        // Logic to find existing conversation based on:
        // - in_reply_to header
        // - references header
        // - subject line matching
        // - same customer email
        
        return null; // Placeholder
    }
    
    /**
     * Generate unique conversation ID
     */
    private function generateConversationUid()
    {
        return 'CONV-' . strtoupper(substr(md5(uniqid()), 0, 8));
    }
    
    /**
     * Apply automation rules
     */
    private function applyAutomationRules()
    {
        // Check for spam
        if ($this->email->spam_score > 5) {
            $this->email->update(['status' => 'spam']);
            return;
        }
        
        // Auto-assign based on rules
        // - Keywords in subject
        // - From domain
        // - Time of day
        // etc.
    }
    
    /**
     * Check and send auto-response if configured
     */
    private function checkAutoResponse()
    {
        // Check if auto-response is enabled for this email account
        // Check business hours
        // Check if customer already received auto-response recently
        // Send auto-response if applicable
    }
    
    /**
     * Send notifications to team members
     */
    private function sendNotifications()
    {
        // Notify assigned agent
        // Notify team leads for high priority emails
        // Send webhook notifications if configured
    }
}
