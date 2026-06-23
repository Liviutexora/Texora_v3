<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('backup:run --only-db')->daily();

// Send appointment reminders every morning at 8 AM for the next day's appointments
Schedule::command('appointments:send-reminders')->dailyAt('08:00');

// Prune activity log entries older than 90 days to keep the table lean
Schedule::command('activitylog:clean --days=90')->weekly();

// Pull external Google Calendar events into blocked slot overrides
Schedule::command('calendar:sync-busy')->hourly();