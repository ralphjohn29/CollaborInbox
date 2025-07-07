<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Events\TestEvent;

class TestWebsocket extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'websocket:test {message?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test event to the WebSocket server';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $message = $this->argument('message') ?? 'Test WebSocket message from ' . now();
        
        event(new TestEvent($message));
        
        $this->info("Sent test message: \"$message\" to WebSocket server");
        
        return Command::SUCCESS;
    }
} 