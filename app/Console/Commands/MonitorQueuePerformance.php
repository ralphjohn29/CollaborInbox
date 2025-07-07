<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\QueueMonitor;
use Illuminate\Support\Facades\Log;

class MonitorQueuePerformance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:monitor {--alert : Check for alert conditions}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor queue performance and check for issues';

    /**
     * Execute the console command.
     */
    public function handle(QueueMonitor $queueMonitor)
    {
        if ($this->option('alert')) {
            $this->info('Checking queue metrics for alert conditions...');
            $queueMonitor->checkAlertConditions();
            $this->info('Alert check completed.');
            return;
        }
        
        // Show queue metrics
        $metrics = $queueMonitor->getQueueMetrics();
        
        $this->info('Queue Metrics Summary:');
        $this->table(
            ['Queue', 'Processed', 'Success', 'Failed', 'Failure Rate', 'Avg Time (s)'],
            $this->formatMetricsForTable($metrics)
        );
        
        // Show recent failed jobs
        $failedJobs = $queueMonitor->getRecentFailedJobs(5);
        
        if (!empty($failedJobs)) {
            $this->info('Recent Failed Jobs:');
            $this->table(
                ['UUID', 'Queue', 'Failed At', 'Exception'],
                $this->formatFailedJobsForTable($failedJobs)
            );
        } else {
            $this->info('No recent failed jobs found.');
        }
    }
    
    /**
     * Format metrics data for table display
     * 
     * @param array $metrics
     * @return array
     */
    protected function formatMetricsForTable(array $metrics): array
    {
        $rows = [];
        
        foreach ($metrics as $queue => $data) {
            $total = $data['total'];
            $success = $data['success'];
            $failed = $data['failed'];
            $failureRate = $total > 0 ? round(($failed / $total) * 100, 2) . '%' : '0%';
            $avgTime = $data['avg_execution_time'] ?? 'N/A';
            
            $rows[] = [
                $queue,
                $total,
                $success,
                $failed,
                $failureRate,
                $avgTime
            ];
        }
        
        return $rows;
    }
    
    /**
     * Format failed jobs data for table display
     * 
     * @param array $failedJobs
     * @return array
     */
    protected function formatFailedJobsForTable(array $failedJobs): array
    {
        $rows = [];
        
        foreach ($failedJobs as $job) {
            $rows[] = [
                $job->uuid,
                $job->queue,
                $job->failed_at,
                $this->truncateString($job->exception, 50)
            ];
        }
        
        return $rows;
    }
    
    /**
     * Truncate a string to a specific length
     * 
     * @param string $string
     * @param int $length
     * @return string
     */
    protected function truncateString(string $string, int $length): string
    {
        if (strlen($string) <= $length) {
            return $string;
        }
        
        return substr($string, 0, $length) . '...';
    }
} 