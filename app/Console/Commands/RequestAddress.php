<?php

namespace App\Console\Commands;

use App\Http\Controllers\ImportController;
use App\Models\Defibrillator;
use Illuminate\Console\Command;

class RequestAddress extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aed:update-address {--aed= : The AED ID to request Nominatim for} {--overwrite : Overwrite all AEDs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update address details for defibrillators from Nominatim';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('aed')) {
            $aedId = $this->option('aed');
            $this->info("Requesting Nominatim for AED ID: {$aedId}");
            $defibrillator = Defibrillator::where('id', $aedId)->first();
            $result = ImportController::updateNominatim($defibrillator);
            if ($result) {
                $this->info('Found address: ' . $result['full_address']);
            } else {
                $this->error('Failed to update Nominatim data.');
            }
        } else {
            $this->info("No ID provided - updating all AEDs");
            if ($this->option('overwrite')) {
                $defibrillators = Defibrillator::all();
                $this->info("Overwrite option provided - updating all AEDs");
            } else {
                $defibrillators = Defibrillator::where('address', null)->get();
            }

            if ($defibrillators->isEmpty()) {
                $this->info('No defibrillators found without Nominatim data. Run command with --overwrite to update all defibrillators.');
                return;
            }

            $progressBar = $this->output->createProgressBar($defibrillators->count());
            $progressBar->start();

            $progressBar->setFormat(<<<EOT
            [%bar%] %current%/%max% (%percent:3s%%) %message%
            EOT);

            $progressBar->setMessage('Starting...');
            $progressBar->start();

            foreach ($defibrillators as $defibrillator) {
                $progressBar->setMessage("Processing AED ID: {$defibrillator->id}");
                $result = ImportController::updateNominatim($defibrillator);

                if ($result) {
                    $progressBar->setMessage("Found address: {$result['full_address']}");
                } else {
                    $progressBar->setMessage('Failed to update Nominatim data.');
                }

                $progressBar->advance();
            }

            $progressBar->finish();
        }
    }
}
