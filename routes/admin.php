<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\AdminPassword;
use App\Http\Controllers\AdminPanelController;

Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminPanelController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AdminPanelController::class, 'login'])->name('admin.login.submit');
    Route::post('/logout', [AdminPanelController::class, 'logout'])->name('admin.logout');

    Route::get('/', function () {
        if (cookie('admin_pass')) {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('admin.login');
    });

    Route::middleware([AdminPassword::class])->group(function () {
        Route::get('/dashboard', [AdminPanelController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/defibrillators', [AdminPanelController::class, 'defibrillators'])->name('admin.defibrillators');
        Route::get('/imports', [AdminPanelController::class, 'imports'])->name('admin.imports');
        Route::get('/operators', [AdminPanelController::class, 'operators'])->name('admin.operators');
        Route::get('/access-tokens', [AdminPanelController::class, 'accessTokens'])->name('admin.access-tokens');

        // API
        Route::post('api/imports/trigger', [AdminPanelController::class, 'triggerImport'])->name('admin.imports.trigger');
    });
});