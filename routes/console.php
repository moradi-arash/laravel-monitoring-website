<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\SiteSetting;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule the website monitoring command to run every minute
// The command itself will check if it should run based on the configured interval
Schedule::command('monitor:websites')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

// Schedule clearing old errors every 24 hours
Schedule::command('websites:clear-old-errors')
    ->daily()
    ->at('02:00')
    ->withoutOverlapping();
