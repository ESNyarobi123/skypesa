<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Services\TaskLockService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule subscription expiry check daily at midnight
Schedule::command('subscriptions:expire')->dailyAt('00:00');

// Cleanup expired task locks every 5 minutes
Schedule::call(function () {
    $lockService = app(TaskLockService::class);
    $cleaned = $lockService->cleanupExpiredLocks(30); // 30 minutes max
    if ($cleaned > 0) {
        \Log::info("Cleaned up {$cleaned} expired task locks");
    }
})->everyFiveMinutes();
