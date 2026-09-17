<?php

namespace App\Jobs;

use App\Http\Controllers\ImportController;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

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
        ImportController::importDefibrillators(
            $this->fullImport,
            $this->overrideRegion,
            $this->uuid
        );
    }
}