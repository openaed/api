<?php

namespace App\Jobs;

use App\Http\Controllers\ImportController;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ImportDefibrillators implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public bool $fullImport,
        public ?string $overrideRegion = null,
        public ?string $uuid = null
    ) {
    }

    public function handle(): void
    {
        Log::info('Import timezone debug', [
            'php_timezone' => date_default_timezone_get(),
            'laravel_timezone' => config('app.timezone'),
            'now' => now()->toDateTimeString(),
            'now_timezone' => now()->timezoneName,
        ]);
        ImportController::importDefibrillators(
            $this->fullImport,
            $this->overrideRegion,
            $this->uuid
        );
    }
}