<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmailAccount;
use App\Models\Disposition;
use App\Models\Email;
use App\Models\EmailAttachment;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;

class InboxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get the first tenant
        $tenant = Tenant::first();
        
        if (!$tenant) {
            $this->command->error('No tenant found. Please create a tenant first.');
            return;
        }

        $this->command->info('Creating inbox data for tenant: ' . $tenant->name);

        // Create dispositions
        $dispositions = [
            ['name' => 'Sales Inquiry', 'color' => '#22c55e', 'description' => 'Potential customer inquiries about products or services'],
            ['name' => 'Support Request', 'color' => '#ef4444', 'description' => 'Customer support and help requests'],
            ['name' => 'Billing Question', 'color' => '#f59e0b', 'description' => 'Questions about invoices, payments, or billing'],
            ['name' => 'General Info', 'color' => '#3b82f6', 'description' => 'General information requests'],
            ['name' => 'Partnership', 'color' => '#a855f7', 'description' => 'Business partnership and collaboration inquiries'],
            ['name' => 'Feedback', 'color' => '#06b6d4', 'description' => 'Customer feedback and suggestions'],
        ];

        $createdDispositions = [];
        foreach ($dispositions as $index => $disposition) {
            $createdDispositions[] = Disposition::create([
                'tenant_id' => $tenant->id,
                'name' => $disposition['name'],
                'color' => $disposition['color'],
                'description' => $disposition['description'],
                'sort_order' => $index,
                'is_active' => true,
            ]);
        }

        // Create email accounts
        $emailAccounts = [
            [
                'email_prefix' => 'sales',
                'display_name' => 'Sales Team',
                'description' => 'Main sales email account',
            ],
            [
                'email_prefix' => 'support',
                'display_name' => 'Customer Support',
                'description' => 'Customer support email account',
            ],
            [
                'email_prefix' => 'info',
                'display_name' => 'General Information',
                'description' => 'General inquiries email account',
            ],
        ];

        $domain = explode('.', $tenant->domain)[0] . '.com';
        $createdAccounts = [];
        
        foreach ($emailAccounts as $account) {
            $createdAccounts[] = EmailAccount::create([
                'tenant_id' => $tenant->id,
                'email_prefix' => $account['email_prefix'],
                'email_address' => $account['email_prefix'] . '@' . $domain,
                'display_name' => $account['display_name'],
                'description' => $account['description'],
                'is_active' => true,
                'incoming_server_type' => 'imap',
                'incoming_server_host' => 'imap.gmail.com',
                'incoming_server_port' => 993,
                'incoming_server_username' => $account['email_prefix'] . '@' . $domain,
                'incoming_server_password' => 'demo_password',
                'incoming_server_ssl' => true,
                'outgoing_server_host' => 'smtp.gmail.com',
                'outgoing_server_port' => 587,
                'outgoing_server_username' => $account['email_prefix'] . '@' . $domain,
                'outgoing_server_password' => 'demo_password',
                'outgoing_server_ssl' => true,
            ]);
        }

        // Get users for assignment
        $users = User::where('tenant_id', $tenant->id)->get();

        // Create sample emails
        $sampleEmails = [
            [
                'from_name' => 'John Smith',
                'from_email' => 'john.smith@example.com',
                'subject' => 'Interested in Enterprise Plan',
                'body_text' => 'Hi,\n\nI am interested in learning more about your Enterprise plan. We are a company of 50 employees and need a solution for team collaboration.\n\nCan you please provide more information about pricing and features?\n\nBest regards,\nJohn Smith\nCEO, Tech Corp',
                'body_html' => '<p>Hi,</p><p>I am interested in learning more about your Enterprise plan. We are a company of 50 employees and need a solution for team collaboration.</p><p>Can you please provide more information about pricing and features?</p><p>Best regards,<br>John Smith<br>CEO, Tech Corp</p>',
                'account_index' => 0, // sales
                'disposition_index' => 0, // Sales Inquiry
                'status' => 'unread',
                'is_starred' => true,
            ],
            [
                'from_name' => 'Sarah Johnson',
                'from_email' => 'sarah.j@techstartup.io',
                'subject' => 'Cannot access my account',
                'body_text' => 'Hello Support,\n\nI have been trying to log into my account but keep getting an error message. My username is sarah.j@techstartup.io.\n\nPlease help me resolve this issue as soon as possible.\n\nThank you,\nSarah',
                'body_html' => '<p>Hello Support,</p><p>I have been trying to log into my account but keep getting an error message. My username is sarah.j@techstartup.io.</p><p>Please help me resolve this issue as soon as possible.</p><p>Thank you,<br>Sarah</p>',
                'account_index' => 1, // support
                'disposition_index' => 1, // Support Request
                'status' => 'read',
                'is_important' => true,
            ],
            [
                'from_name' => 'Michael Chen',
                'from_email' => 'mchen@globalcorp.com',
                'subject' => 'Invoice question - Order #12345',
                'body_text' => 'Dear Billing Team,\n\nI received invoice #12345 but the amount seems incorrect. We agreed on a 20% discount for annual payment but this is not reflected.\n\nCould you please check and send a corrected invoice?\n\nRegards,\nMichael Chen\nFinance Manager',
                'body_html' => '<p>Dear Billing Team,</p><p>I received invoice #12345 but the amount seems incorrect. We agreed on a 20% discount for annual payment but this is not reflected.</p><p>Could you please check and send a corrected invoice?</p><p>Regards,<br>Michael Chen<br>Finance Manager</p>',
                'account_index' => 0, // sales (billing goes to sales in this example)
                'disposition_index' => 2, // Billing Question
                'status' => 'unread',
            ],
            [
                'from_name' => 'Emily Davis',
                'from_email' => 'emily@designstudio.com',
                'subject' => 'Partnership Opportunity',
                'body_text' => 'Hello,\n\nWe are a design studio specializing in UX/UI and would love to explore partnership opportunities with your company.\n\nWe have worked with several SaaS companies and believe we could add value to your product.\n\nWould you be interested in scheduling a call?\n\nBest,\nEmily Davis\nCreative Director',
                'body_html' => '<p>Hello,</p><p>We are a design studio specializing in UX/UI and would love to explore partnership opportunities with your company.</p><p>We have worked with several SaaS companies and believe we could add value to your product.</p><p>Would you be interested in scheduling a call?</p><p>Best,<br>Emily Davis<br>Creative Director</p>',
                'account_index' => 2, // info
                'disposition_index' => 4, // Partnership
                'status' => 'read',
                'has_attachments' => true,
                'attachment_count' => 1,
            ],
            [
                'from_name' => 'Robert Wilson',
                'from_email' => 'rwilson@email.com',
                'subject' => 'Great product!',
                'body_text' => 'Just wanted to say that your product has transformed how our team works. The collaboration features are exactly what we needed.\n\nOne suggestion: it would be great to have a dark mode option.\n\nKeep up the great work!\n\nRobert',
                'body_html' => '<p>Just wanted to say that your product has transformed how our team works. The collaboration features are exactly what we needed.</p><p>One suggestion: it would be great to have a dark mode option.</p><p>Keep up the great work!</p><p>Robert</p>',
                'account_index' => 2, // info
                'disposition_index' => 5, // Feedback
                'status' => 'read',
                'is_starred' => true,
            ],
            [
                'from_name' => 'Lisa Anderson',
                'from_email' => 'lisa@marketing.com',
                'subject' => 'Question about API access',
                'body_text' => 'Hi,\n\nDoes your platform provide API access? We would like to integrate it with our existing CRM system.\n\nWhat are the available endpoints and rate limits?\n\nThanks,\nLisa Anderson',
                'body_html' => '<p>Hi,</p><p>Does your platform provide API access? We would like to integrate it with our existing CRM system.</p><p>What are the available endpoints and rate limits?</p><p>Thanks,<br>Lisa Anderson</p>',
                'account_index' => 1, // support
                'disposition_index' => 1, // Support Request  
                'status' => 'unread',
            ],
        ];

        foreach ($sampleEmails as $index => $emailData) {
            $email = Email::create([
                'tenant_id' => $tenant->id,
                'email_account_id' => $createdAccounts[$emailData['account_index']]->id,
                'assigned_to' => $index < 3 && $users->count() > 0 ? $users->random()->id : null,
                'disposition_id' => $createdDispositions[$emailData['disposition_index']]->id,
                'message_id' => '<' . uniqid() . '@example.com>',
                'from_email' => $emailData['from_email'],
                'from_name' => $emailData['from_name'],
                'to_email' => $createdAccounts[$emailData['account_index']]->email_address,
                'subject' => $emailData['subject'],
                'body_html' => $emailData['body_html'],
                'body_text' => $emailData['body_text'],
                'status' => $emailData['status'],
                'is_starred' => $emailData['is_starred'] ?? false,
                'is_important' => $emailData['is_important'] ?? false,
                'has_attachments' => $emailData['has_attachments'] ?? false,
                'attachment_count' => $emailData['attachment_count'] ?? 0,
                'received_at' => Carbon::now()->subHours(rand(1, 72)),
                'read_at' => $emailData['status'] === 'read' ? Carbon::now()->subHours(rand(1, 24)) : null,
            ]);

            // Add sample attachment for partnership email
            if ($emailData['has_attachments'] ?? false) {
                EmailAttachment::create([
                    'email_id' => $email->id,
                    'filename' => 'portfolio.pdf',
                    'mime_type' => 'application/pdf',
                    'size' => 2457600, // 2.4 MB
                    'storage_path' => 'email-attachments/' . $email->id . '/portfolio.pdf',
                ]);
            }
        }

        $this->command->info('Inbox seeding completed successfully!');
        $this->command->info('Created:');
        $this->command->info('- ' . count($createdDispositions) . ' dispositions');
        $this->command->info('- ' . count($createdAccounts) . ' email accounts');
        $this->command->info('- ' . count($sampleEmails) . ' sample emails');
    }
}
