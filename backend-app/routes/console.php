<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Housekeeping (needs `php artisan schedule:work` locally, or a cron entry online):
// remove expired app sign-ins and security records older than 90 days.
Schedule::command('sanctum:prune-expired --hours=24')->daily();
Schedule::command('model:prune')->daily();
