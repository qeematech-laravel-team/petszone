<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule subscription management commands
Schedule::command('subscriptions:expire')
    ->daily()
    ->at('00:00')
    ->timezone('UTC')
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('subscriptions:activate-scheduled')
    ->daily()
    ->at('00:01')
    ->timezone('UTC')
    ->withoutOverlapping()
    ->runInBackground();
