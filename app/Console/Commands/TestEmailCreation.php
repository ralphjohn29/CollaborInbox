<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Email;
use App\Models\Workspace;

class TestEmailCreation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test {--count=5 : Number of test emails to create}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create test emails for development';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = $this->option('count');
        $workspace = Workspace::first();
        
        if (!$workspace) {
            $this->error('No workspace found. Please create a workspace first.');
            return 1;
        }
        
        $this->info("Creating {$count} test emails...");
        
        $testEmails = [
            [
                'from' => 'John Doe <john.doe@example.com>',
                'subject' => 'Need help with my order #12345',
                'body' => 'Hi, I placed an order yesterday but haven\'t received a confirmation email. Can you please check?',
            ],
            [
                'from' => 'Jane Smith <jane.smith@company.com>',
                'subject' => 'Partnership opportunity',
                'body' => 'Hello, we\'re interested in partnering with your company. We have a large customer base that could benefit from your services.',
            ],
            [
                'from' => 'Support Request <customer@gmail.com>',
                'subject' => 'Cannot login to my account',
                'body' => 'I\'ve been trying to login but keep getting an error. My username is customer@gmail.com. Please help!',
            ],
            [
                'from' => 'Mike Johnson <mike@startup.io>',
                'subject' => 'Feature request: Dark mode',
                'body' => 'Love your product! Would be great if you could add a dark mode option. Many of us work late nights.',
            ],
            [
                'from' => 'Sales Inquiry <buyer@business.com>',
                'subject' => 'Pricing for enterprise plan',
                'body' => 'We\'re a team of 50+ people. What\'s the pricing for enterprise? Do you offer volume discounts?',
            ],
        ];
        
        for ($i = 0; $i < $count; $i++) {
            $emailData = $testEmails[$i % count($testEmails)];
            
            // Parse from field
            preg_match('/^(.+?)\s*<(.+?)>/', $emailData['from'], $matches);
            $fromName = $matches[1] ?? null;
            $fromEmail = $matches[2] ?? $emailData['from'];
            
            // Create email
            $email = Email::create([
                'workspace_id' => $workspace->id,
                'conversation_id' => 1, // Using default conversation ID for now
                'message_id' => '<' . uniqid() . '@example.com>',
                'from_email' => $fromEmail,
                'from_name' => $fromName,
                'to_email' => json_encode(['support@collaborinbox.test']),
                'subject' => $emailData['subject'],
                'body_text' => $emailData['body'],
                'body_html' => '<p>' . nl2br(htmlspecialchars($emailData['body'])) . '</p>',
                'direction' => 'inbound',
                'status' => 'pending',
                'is_starred' => rand(0, 1) == 1,
                'is_important' => rand(0, 3) == 0,
                'has_attachments' => false,
                'attachment_count' => 0,
                'received_at' => now()->subMinutes(rand(0, 1440)), // Random time in last 24 hours
            ]);
            
            $this->line("Created: {$emailData['subject']} from {$fromEmail}");
        }
        
        $this->info("Successfully created {$count} test emails!");
        
        return 0;
    }
}
