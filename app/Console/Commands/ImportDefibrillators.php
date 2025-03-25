<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\ImportController;

class ImportDefibrillators extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aed:import {--full : Request a full import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Trigger a defibrillator import';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting import...');
        $isFullImport = $this->option('full');
        if($isFullImport) {
            $this->info('Full import requested. This may take a while.');
        }
        try {
            $import = ImportController::importDefibrillators($isFullImport);
            $this->info('Import successful');
            $this->info('Defibrillators imported: ' . $import->defibrillators);
            return 0;
        } catch (\Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage());
            return 1;
        }
    }
}
