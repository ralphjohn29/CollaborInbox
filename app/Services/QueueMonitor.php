<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class QueueMonitor
{
    /**
     * Record a job execution event
     * 
     * @param string $queue
     * @param string $jobName
     * @param bool $success
     * @param float $executionTime
     * @param string|null $tenantId
     * @return void
     */
    public function recordJobExecution(string $queue, string $jobName, bool $success, float $executionTime, ?string $tenantId = null): void
    {
        $date = now()->format('Y-m-d');
        
        // Store metrics in Redis for real-time monitoring
        Redis::hincrby("queue:metrics:{$date}:{$queue}", 'total', 1);
        
        if ($success) {
            Redis::hincrby("queue:metrics:{$date}:{$queue}", 'success', 1);
        } else {
            Redis::hincrby("queue:metrics:{$date}:{$queue}", 'failed', 1);
        }
        
        // Track average execution time
        $currentTotal = Redis::hget("queue:metrics:{$date}:{$queue}", 'execution_time_total') ?? 0;
        $currentCount = Redis::hget("queue:metrics:{$date}:{$queue}", 'execution_time_count') ?? 0;
        
        Redis::hincrbyfloat("queue:metrics:{$date}:{$queue}", 'execution_time_total', $executionTime);
        Redis::hincrby("queue:metrics:{$date}:{$queue}", 'execution_time_count', 1);
        
        // Track by job name 
        Redis::hincrby("queue:metrics:{$date}:jobs:{$jobName}", 'total', 1);
        
        if ($success) {
            Redis::hincrby("queue:metrics:{$date}:jobs:{$jobName}", 'success', 1);
        } else {
            Redis::hincrby("queue:metrics:{$date}:jobs:{$jobName}", 'failed', 1);
        }
        
        // If tenant-specific, track per tenant
        if ($tenantId) {
            Redis::hincrby("queue:metrics:{$date}:tenant:{$tenantId}", 'total', 1);
            
            if ($success) {
                Redis::hincrby("queue:metrics:{$date}:tenant:{$tenantId}", 'success', 1);
            } else {
                Redis::hincrby("queue:metrics:{$date}:tenant:{$tenantId}", 'failed', 1);
            }
        }
        
        // Set expiry for metrics (30 days)
        Redis::expire("queue:metrics:{$date}:{$queue}", 60 * 60 * 24 * 30);
        Redis::expire("queue:metrics:{$date}:jobs:{$jobName}", 60 * 60 * 24 * 30);
        
        if ($tenantId) {
            Redis::expire("queue:metrics:{$date}:tenant:{$tenantId}", 60 * 60 * 24 * 30);
        }
    }
    
    /**
     * Check for alert conditions and send notifications if necessary
     * 
     * @return void
     */
    public function checkAlertConditions(): void
    {
        $date = now()->format('Y-m-d');
        
        // Check for queues with high failure rates
        $queues = ['emails', 'email-processing', 'default'];
        
        foreach ($queues as $queue) {
            $total = Redis::hget("queue:metrics:{$date}:{$queue}", 'total') ?? 0;
            $failed = Redis::hget("queue:metrics:{$date}:{$queue}", 'failed') ?? 0;
            
            // If we have meaningful data and a high failure rate
            if ($total > 10 && ($failed / $total) > 0.2) {
                // Log high failure rate
                Log::warning("High failure rate detected in queue {$queue}", [
                    'total' => $total,
                    'failed' => $failed,
                    'failure_rate' => $failed / $total,
                ]);
                
                // In a real application, you could notify administrators:
                // Notification::route('mail', 'admin@example.com')
                //     ->notify(new QueuePerformanceNotification($queue, $total, $failed));
            }
        }
        
        // Check for slow jobs
        foreach ($queues as $queue) {
            $timeTotal = Redis::hget("queue:metrics:{$date}:{$queue}", 'execution_time_total') ?? 0;
            $timeCount = Redis::hget("queue:metrics:{$date}:{$queue}", 'execution_time_count') ?? 0;
            
            if ($timeCount > 0) {
                $avgTime = $timeTotal / $timeCount;
                
                // Alert if average processing time is over 5 seconds
                if ($avgTime > 5) {
                    Log::warning("Slow job processing detected in queue {$queue}", [
                        'average_time' => $avgTime,
                        'jobs_processed' => $timeCount,
                    ]);
                }
            }
        }
    }
    
    /**
     * Get recent failed jobs from the database
     * 
     * @param int $limit
     * @return array
     */
    public function getRecentFailedJobs(int $limit = 50): array
    {
        try {
            return DB::table('failed_jobs')
                ->orderBy('failed_at', 'desc')
                ->limit($limit)
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            Log::error("Failed to retrieve failed jobs: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Retry failed jobs by queue
     * 
     * @param string $queue
     * @return int Number of jobs retried
     */
    public function retryFailedJobsByQueue(string $queue): int
    {
        try {
            $failedJobs = DB::table('failed_jobs')
                ->where('queue', $queue)
                ->orderBy('failed_at', 'asc')
                ->get();
                
            $retried = 0;
            
            foreach ($failedJobs as $job) {
                \Artisan::call('queue:retry', ['id' => $job->uuid]);
                $retried++;
            }
            
            return $retried;
        } catch (\Exception $e) {
            Log::error("Failed to retry jobs: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get queue performance metrics for a specific date
     * 
     * @param string|null $date Format: Y-m-d
     * @return array
     */
    public function getQueueMetrics(?string $date = null): array
    {
        $date = $date ?? now()->format('Y-m-d');
        $metrics = [];
        
        $queues = ['emails', 'email-processing', 'default'];
        
        foreach ($queues as $queue) {
            $queueKey = "queue:metrics:{$date}:{$queue}";
            
            if (Redis::exists($queueKey)) {
                $metrics[$queue] = [
                    'total' => (int)Redis::hget($queueKey, 'total'),
                    'success' => (int)Redis::hget($queueKey, 'success'),
                    'failed' => (int)Redis::hget($queueKey, 'failed'),
                    'avg_execution_time' => null,
                ];
                
                $timeTotal = Redis::hget($queueKey, 'execution_time_total');
                $timeCount = Redis::hget($queueKey, 'execution_time_count');
                
                if ($timeCount > 0) {
                    $metrics[$queue]['avg_execution_time'] = round($timeTotal / $timeCount, 4);
                }
            }
        }
        
        return $metrics;
    }
} 