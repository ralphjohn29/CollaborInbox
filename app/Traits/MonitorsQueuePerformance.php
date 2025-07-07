<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;
use App\Services\QueueMonitor;
use Throwable;

trait MonitorsQueuePerformance
{
    /**
     * Start time of job execution
     * 
     * @var float
     */
    protected $startTime;
    
    /**
     * Record the start time of the job
     * 
     * @return void
     */
    public function recordStartTime(): void
    {
        $this->startTime = microtime(true);
    }
    
    /**
     * Calculate execution time in seconds
     * 
     * @return float
     */
    protected function calculateExecutionTime(): float
    {
        if (!isset($this->startTime)) {
            return 0.0;
        }
        
        return microtime(true) - $this->startTime;
    }
    
    /**
     * Record successful job completion
     * 
     * @return void
     */
    public function recordSuccessfulExecution(): void
    {
        try {
            $queueMonitor = app(QueueMonitor::class);
            
            $queue = $this->queue ?? 'default';
            $jobName = get_class($this);
            $executionTime = $this->calculateExecutionTime();
            $tenantId = $this->tenantId ?? null;
            
            $queueMonitor->recordJobExecution($queue, $jobName, true, $executionTime, $tenantId);
        } catch (Throwable $e) {
            Log::error("Failed to record job metrics: " . $e->getMessage());
        }
    }
    
    /**
     * Record failed job
     * 
     * @param Throwable $exception
     * @return void
     */
    public function recordFailedExecution(Throwable $exception): void
    {
        try {
            $queueMonitor = app(QueueMonitor::class);
            
            $queue = $this->queue ?? 'default';
            $jobName = get_class($this);
            $executionTime = $this->calculateExecutionTime();
            $tenantId = $this->tenantId ?? null;
            
            $queueMonitor->recordJobExecution($queue, $jobName, false, $executionTime, $tenantId);
            
            Log::error("Job failed: " . $jobName, [
                'tenant_id' => $tenantId,
                'queue' => $queue,
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]);
        } catch (Throwable $e) {
            Log::error("Failed to record job failure metrics: " . $e->getMessage());
        }
    }
} 