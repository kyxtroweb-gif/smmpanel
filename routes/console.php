<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Artisan inspiration
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Scheduled tasks
// Note: These require cron setup: * * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1

// Sync order statuses every minute
Schedule::command('orders:sync-status')->everyMinute();

// Process dripfeeds every minute
Schedule::command('orders:process-dripfeeds')->everyMinute();

// Process subscriptions every 5 minutes
Schedule::command('orders:process-subscriptions')->everyFiveMinutes();

// Sync provider balances every hour
Schedule::command('providers:sync-balances')->hourly();

// Expire pending payments older than 24 hours
Schedule::command('payments:expire')->everyTenMinutes();
