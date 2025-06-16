<?php

namespace App\Http\Controllers;

use \App\Models\Defibrillator;
use \App\Models\Operator;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    /**
     * Get statistics about the defibrillators
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStats(Request $request)
    {
        $lastImport = \App\Models\Import::count() > 0 ? \App\Models\Import::orderBy('started_at', 'desc')->first()->started_at : null;

        $withoutLocationCount = Defibrillator::whereNull('location')
            ->orWhere('location', '')
            ->count();

        // Defibrillators where location is not null or empty
        $withLocationCount = Defibrillator::whereNotNull('location')
            ->where('location', '!=', '')
            ->count();

        $stats = [
            'total_defibrillators' => Defibrillator::count(),
            'defibrillators' => [
                'with_location' => $withLocationCount,
                'without_location' => $withoutLocationCount,
                'available_247' => Defibrillator::where('opening_hours', '24/7')->count(),
            ],
            'total_operators' => Operator::count(),
            'operators' => [
                'with_website' => Operator::whereNotNull('website')->count(),
                'with_email' => Operator::whereNotNull('email')->count(),
                'with_phone' => Operator::whereNotNull('phone')->count(),
            ],
        ];

        return response()->json($stats);
    }
}