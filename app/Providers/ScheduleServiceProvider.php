<?php

namespace App\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class ScheduleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(Schedule $schedule): void
    {
        $schedule->call(function () {
            Log::info('doing cron');
        })->everyMinute();
    }
}
