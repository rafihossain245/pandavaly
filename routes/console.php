<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
| Courier handover.
|
| push-pending is the safety net: the observer already queues a job the moment
| an order reaches the fulfilment status, but that needs a live queue worker.
| This makes a single cron entry sufficient. Both are idempotent, so running
| them together never ships a parcel twice.
|
| The whole schedule needs one cron on the server:
|   * * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
*/
Schedule::command('courier:push-pending')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('courier:sync-status --advance-orders')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground();
