<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule the website monitoring command to run every 10 minutes
Schedule::command('monitor:websites')
    ->everyTenMinutes()
    ->withoutOverlapping()
    ->runInBackground();
