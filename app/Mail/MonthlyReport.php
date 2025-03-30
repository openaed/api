<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MonthlyReport extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The first day of the month.
     */
    public Carbon $firstDayOfMonth;

    /**
     * The last day of the month.
     */
    public Carbon $lastDayOfMonth;

    /**
     * The number of authenticated requests.
     */
    public int $authenticatedRequests;

    /**
     * The total number of requests.
     */
    public int $totalRequests;

    /**
     * The total number of defibrillators.
     */
    public int $totalDefibrillators;

    /**
     * The number of new defibrillators.
     */
    public int $newDefibrillators;

    /**
     * The total number of access tokens.
     */
    public int $totalAccessTokens;

    /**
     * The number of new access tokens.
     */
    public int $newAccessTokens;

    /**
     * The newest access token assignees.
     */
    public $newestAccessTokenAssignees;

    /**
     * Create a new message instance.
     */
    public function __construct(
        Carbon $firstDayOfMonth,
        Carbon $lastDayOfMonth,
        int $authenticatedRequests,
        int $totalRequests,
        int $totalDefibrillators,
        int $newDefibrillators,
        int $totalAccessTokens,
        int $newAccessTokens,
        $newestAccessTokenAssignees
    ) {
        $this->firstDayOfMonth = $firstDayOfMonth;
        $this->lastDayOfMonth = $lastDayOfMonth;
        $this->authenticatedRequests = $authenticatedRequests;
        $this->totalRequests = $totalRequests;
        $this->totalDefibrillators = $totalDefibrillators;
        $this->newDefibrillators = $newDefibrillators;
        $this->totalAccessTokens = $totalAccessTokens;
        $this->newAccessTokens = $newAccessTokens;
        $this->newestAccessTokenAssignees = $newestAccessTokenAssignees;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Monthly report',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.monthly-report',
            with: [
                'authenticatedRequests' => $this->authenticatedRequests,
                'totalRequests' => $this->totalRequests,
                'totalDefibrillators' => $this->totalDefibrillators,
                'newDefibrillators' => $this->newDefibrillators,
                'newestAccessTokens' => $this->newestAccessTokenAssignees,
                'totalAccessTokens' => $this->totalAccessTokens,
                'newAccessTokens' => $this->newAccessTokens,
                'recipient' => config('mail.monitoring_recipient'),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}