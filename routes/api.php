<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\ValidateAccessToken;
use App\Http\Controllers\DefibrillatorController;

Route::middleware(ValidateAccessToken::class)->group(function () {
    Route::get('/info', function (Request $request) {
        $info = [
            'version' => '2025.1.0',
            'datetime' => now(),
            'token' => [
                'expires_at' => $request->attributes->get('access_token')->expires_at,
                'assigned_to' => $request->attributes->get('access_token')->assigned_to,
                'scope' => $request->attributes->get('access_token')->scope,
                'last_used_at' => $request->attributes->get('access_token')->last_used_at
            ]
        ];

        return response()->json($info);
    });

    Route::get('/defibrillators/nearby/{latitude}/{longitude}/{radius}', [DefibrillatorController::class, 'getNearby']);
    Route::get('/defibrillators/area', [DefibrillatorController::class, 'getInArea']);
    Route::get('/defibrillators/{id}', [DefibrillatorController::class, 'getOne']);
    Route::get('/defibrillators', [DefibrillatorController::class, 'getAll']);
});