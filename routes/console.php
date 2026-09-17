<?php

use App\Jobs\ImportDefibrillators;

Schedule::call(function () {
    ImportDefibrillators::dispatch(false, null, null);
})->twiceDailyAt(7, 19, 0)->timezone('Europe/Amsterdam')->name('import-defibrillators-morning')
    ->description('Import defibrillators from OpenStreetMap every day at 07:00 and 19:00');

Schedule::call(function () {
    ImportDefibrillators::dispatch(true, null, null);
})->weeklyOn(0, '23:00')->timezone('Europe/Amsterdam')->name('import-defibrillators-weekly')
    ->description('Import all defibrillators from OpenStreetMap every Sunday at 23:00');

Schedule::command('api:send-month-report')
    ->lastDayOfMonth('23:00')
    ->timezone('Europe/Amsterdam')
    ->name('send-month-report')
    ->description('Send monthly report');

// config(app.heartbeat.interval) is a cron expression
// Run the artisan command app:heartbeat
Schedule::command('app:heartbeat')
    ->cron(config('app.heartbeat.interval'))
    ->timezone('Europe/Amsterdam')
    ->name('app-heartbeat')
    ->description('Send heartbeat signal to the monitoring service');