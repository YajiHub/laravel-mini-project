<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;


Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('inventory:check-low-stock')
    ->dailyAt('08:00')
    ->withoutOverlapping();

Schedule::command('backup:run --only-db')
    ->weekly()
    ->sundays()
    ->at('02:00')
    ->withoutOverlapping();

Schedule::command('backup:run --only-files')
    ->weekly()
    ->sundays()
    ->at('02:30')
    ->withoutOverlapping();

Schedule::command('backup:clean')
    ->dailyAt('01:00')
    ->withoutOverlapping();