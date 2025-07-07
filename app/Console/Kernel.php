<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Run the email fetch command every 5 minutes
        $schedule->command('fetch:emails')
                 ->everyFiveMinutes()
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/email-fetch.log'));
                 
        // Check for queue performance issues every 15 minutes
        $schedule->command('queue:monitor --alert')
                 ->everyFifteenMinutes()
                 ->withoutOverlapping();
                 
        // Retry failed jobs from email processing queue once per hour
        $schedule->command('queue:retry-batch --queue=email-processing')
                 ->hourly()
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/queue-retry.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
} 