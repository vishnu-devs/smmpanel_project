<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/**
 * Schedule definitions using Schedule::call() with Artisan::call() inside.
 *
 * This avoids proc_open (which is disabled on many shared hosting providers).
 * Artisan::call() runs the command in-process instead of spawning a subprocess.
 */

// 1. Sync active provider balances and health parameters
Schedule::call(function () {
    Artisan::call('provider:sync-health');
})->everyMinute();

// 2. Synchronize processing order status in batches
Schedule::call(function () {
    Artisan::call('orders:sync-status');
})->everyMinute();

// 3. Notify Admin of unapproved deposits pending > 1 minute
Schedule::call(function () {
    \App\Http\Controllers\PaymentController::notifyAdminPendingDeposits();
})->everyMinute();

// 3. Prune old activity logs and backups daily
Schedule::call(function () {
    Artisan::call('tokens:prune');
})->daily();

// 4. Auto-sync services from providers (every minute)
Schedule::call(function () {
    Artisan::call('provider:sync-services');
})->everyMinute();

// 5. Send weekly inactive customer wallet balance reminder
Schedule::call(function () {
    Artisan::call('reminders:inactive-balance');
})->weekly();
