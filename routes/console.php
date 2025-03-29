<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\ImportController;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    ImportController::importDefibrillators();
})->twiceDailyAt(7, 19, 0)->timezone('Europe/Amsterdam')->name('import-defibrillators-morning')
    ->description('Import defibrillators from OpenStreetMap every day at 07:00 and 19:00');