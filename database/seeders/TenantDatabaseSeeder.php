<?php

namespace Database\Seeders;

use App\Models\Message;
use App\Models\Note;
use App\Models\Setting;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantDatabaseSeeder extends Seeder
{
    /**
     * Seed the tenant's database.
     */
    public function run(): void
    {
        // Create tenant admin user
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@' . tenant()->domain,
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
        
        // Create a manager
        $manager = User::create([
            'name' => 'Manager',
            'email' => 'manager@' . tenant()->domain,
            'password' => Hash::make('password'),
            'role' => 'manager',
            'email_verified_at' => now(),
        ]);
        
        // Create some agents
        $agents = [];
        for ($i = 1; $i <= 3; $i++) {
            $agents[] = User::create([
                'name' => 'Agent ' . $i,
                'email' => 'agent' . $i . '@' . tenant()->domain,
                'password' => Hash::make('password'),
                'role' => 'agent',
                'email_verified_at' => now(),
            ]);
        }
        
        // Create some threads with messages
        for ($i = 1; $i <= 5; $i++) {
            $thread = Thread::create([
                'subject' => 'Test Thread ' . $i,
                'status' => collect(['new', 'assigned', 'closed'])->random(),
                'external_id' => Str::uuid()->toString(),
                'assigned_to_id' => collect($agents)->random()->id,
                'last_activity_at' => now()->subHours(rand(1, 48)),
            ]);
            
            // Add some messages to thread
            for ($j = 1; $j <= rand(1, 5); $j++) {
                $isOutbound = rand(0, 1);
                
                Message::create([
                    'thread_id' => $thread->id,
                    'from_email' => $isOutbound ? $agents[0]->email : 'customer' . $j . '@example.com',
                    'from_name' => $isOutbound ? $agents[0]->name : 'Customer ' . $j,
                    'to' => $isOutbound ? 'customer' . $j . '@example.com' : $manager->email,
                    'subject' => 'RE: ' . $thread->subject,
                    'body_html' => '<p>This is test message ' . $j . ' in thread ' . $i . '</p>',
                    'body_text' => 'This is test message ' . $j . ' in thread ' . $i,
                    'external_id' => Str::uuid()->toString(),
                    'is_outbound' => $isOutbound,
                    'author_id' => $isOutbound ? $agents[0]->id : null,
                    'sent_at' => now()->subHours(rand(1, 24)),
                ]);
            }
            
            // Add some notes to thread
            for ($k = 1; $k <= rand(0, 3); $k++) {
                Note::create([
                    'thread_id' => $thread->id,
                    'user_id' => collect([$admin->id, $manager->id, ...$agents])->random(),
                    'content' => 'This is a test note ' . $k . ' on thread ' . $i,
                    'is_private' => rand(0, 1),
                ]);
            }
        }
        
        // Add some default settings
        Setting::set('company_name', tenant()->name, 'string', 'Company name for display');
        Setting::set('support_email', 'support@' . tenant()->domain, 'string', 'Support email address');
        Setting::set('theme_color', '#4f46e5', 'string', 'Primary theme color');
        Setting::set('enable_notifications', true, 'boolean', 'Enable email notifications');
        Setting::set('working_hours', [
            'monday' => ['09:00', '17:00'],
            'tuesday' => ['09:00', '17:00'],
            'wednesday' => ['09:00', '17:00'],
            'thursday' => ['09:00', '17:00'],
            'friday' => ['09:00', '17:00'],
            'saturday' => null,
            'sunday' => null,
        ], 'json', 'Working hours for SLA calculations');
    }
} 