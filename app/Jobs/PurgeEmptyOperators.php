<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use App\Models\Operator;

class PurgeEmptyOperators implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $operators = Operator::doesntHave('defibrillators')->get();

        Log::info("Found " . count($operators) . " operators with no associated defibrillators. Purging them.");

        foreach ($operators as $operator) {
            Log::info("Deleting operator with ID: {$operator->id} and name: {$operator->name} due to having no associated defibrillators.");
            $operator->delete();
        }
    }
}