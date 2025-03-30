<?php

namespace App\Console\Commands;

use \App\Mail\MonthlyReport;
use \Mail;
use App\Models\AccessToken;
use App\Models\Defibrillator;
use App\Models\EventLog;
use Illuminate\Console\Command;

class SendMonthlyReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:send-monthly-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send monthly report about usage to the monitor';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get the first day of the current month
        $firstDayOfMonth = now()->startOfMonth();
        // Get the last day of the current month
        $lastDayOfMonth = now()->endOfMonth();

        $authenticatedRequests = EventLog::whereBetween('created_at', [$firstDayOfMonth, $lastDayOfMonth])
            ->where('type', 'request')
            ->whereNotNull('access_token')
            ->count();

        $totalRequests = EventLog::whereBetween('created_at', [$firstDayOfMonth, $lastDayOfMonth])
            ->where('type', 'request')
            ->count();

        $totalDefibrillators = Defibrillator::count();
        $newDefibrillators = Defibrillator::whereBetween('created_at', [$firstDayOfMonth, $lastDayOfMonth])->count();

        $totalAccessTokens = AccessToken::count();
        $newAccessTokens = AccessToken::whereBetween('created_at', [$firstDayOfMonth, $lastDayOfMonth])->count();
        $newestAccessTokenAssignees = AccessToken::whereBetween('created_at', [$firstDayOfMonth, $lastDayOfMonth])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        if (config('mail.monitoring_recipient')) {
            Mail::to(config('mail.monitoring_recipient'))
                ->send(new MonthlyReport(
                    $firstDayOfMonth,
                    $lastDayOfMonth,
                    $authenticatedRequests,
                    $totalRequests,
                    $totalDefibrillators,
                    $newDefibrillators,
                    $totalAccessTokens,
                    $newAccessTokens,
                    $newestAccessTokenAssignees
                ));
            $this->info('Monthly report sent successfully.');
        } else {
            $this->error('No monitoring recipient configured in the mail settings.');
        }
    }
}