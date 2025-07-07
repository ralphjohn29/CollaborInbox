<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\QueueMonitor;
use Illuminate\Support\Facades\Log;

class RetryFailedJobsByQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:retry-batch {--queue= : Queue name to retry failed jobs for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retry all failed jobs for a specific queue';

    /**
     * Execute the console command.
     */
    public function handle(QueueMonitor $queueMonitor)
    {
        $queue = $this->option('queue');
        
        if (!$queue) {
            $this->error('You must specify a queue using --queue option');
            return 1;
        }
        
        $this->info("Retrying failed jobs for queue: {$queue}");
        
        try {
            $count = $queueMonitor->retryFailedJobsByQueue($queue);
            
            if ($count === 0) {
                $this->info("No failed jobs found for queue: {$queue}");
            } else {
                $this->info("Successfully retried {$count} failed jobs in queue: {$queue}");
            }
            
            return 0;
        } catch (\Exception $e) {
            $this->error("Error retrying jobs: " . $e->getMessage());
            Log::error("Failed to retry jobs", [
                'queue' => $queue,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return 1;
        }
    }
} 