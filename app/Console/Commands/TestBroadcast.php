<?php

namespace App\Console\Commands;

use App\Models\Thread;
use App\Models\Message;
use App\Services\BroadcastService;
use Illuminate\Console\Command;

class TestBroadcast extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'broadcast:test {tenantId} {eventType=thread-update}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test broadcasting events (thread-update, thread-assigned, new-message)';

    /**
     * The broadcast service.
     *
     * @var BroadcastService
     */
    protected $broadcastService;

    /**
     * Create a new command instance.
     *
     * @param BroadcastService $broadcastService
     */
    public function __construct(BroadcastService $broadcastService)
    {
        parent::__construct();
        $this->broadcastService = $broadcastService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantId = $this->argument('tenantId');
        $eventType = $this->argument('eventType');

        // Find or create a test thread
        $thread = Thread::firstOrCreate(
            ['tenant_id' => $tenantId, 'subject' => 'Test Thread'],
            [
                'status' => 'open',
                'last_activity_at' => now(),
            ]
        );

        $this->info("Using thread ID: {$thread->id}");

        switch ($eventType) {
            case 'thread-update':
                $this->broadcastService->broadcastThreadUpdate($thread, 'updated', ['test' => true]);
                $this->info('Sent thread update broadcast event');
                break;

            case 'thread-assigned':
                $this->broadcastService->broadcastThreadAssignment($thread, null, null);
                $this->info('Sent thread assigned broadcast event');
                break;

            case 'new-message':
                // Create a test message
                $message = Message::create([
                    'content' => 'Test message sent at ' . now(),
                    'thread_id' => $thread->id,
                    'tenant_id' => $tenantId,
                    'is_inbound' => false,
                ]);

                $this->broadcastService->broadcastNewMessage($message);
                $this->info('Sent new message broadcast event');
                break;

            default:
                $this->error("Unknown event type: {$eventType}");
                return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
} 