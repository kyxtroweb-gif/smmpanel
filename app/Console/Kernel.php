<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Sync order statuses every minute
        $schedule->command('orders:sync-status')->everyMinute();

        // Process dripfeeds every minute
        $schedule->command('orders:process-dripfeeds')->everyMinute();

        // Process subscriptions every 5 minutes
        $schedule->command('orders:process-subscriptions')->everyFiveMinutes();

        // Sync provider balances every hour
        $schedule->command('providers:sync-balances')->hourly();

        // Expire old pending payments every 10 minutes
        $schedule->command('payments:expire')->everyTenMinutes();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
    }
}
