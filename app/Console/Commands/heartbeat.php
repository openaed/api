<?php

namespace App\Console\Commands;

use \Illuminate\Support\Facades\Http;
use Illuminate\Console\Command;

class heartbeat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:heartbeat';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends a heartbeat signal to the monitoring service';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $url = config('app.heartbeat.url');
        if (empty($url)) {
            $this->error('Heartbeat URL is not configured.');
            return;
        }

        $response = Http::post($url);

        if ($response->successful()) {
            $this->info('Heartbeat sent successfully.');
        } else {
            $this->error('Failed to send heartbeat: ' . $response->status());
        }
    }
}