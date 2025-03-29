<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Import;

class ImportErrored extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The import instance.
     */
    protected Import $import;
    protected string $errorMessage;

    /**
     * Create a new message instance.
     */
    public function __construct(Import $import, string $errorMessage)
    {
        $this->errorMessage = $errorMessage;
        $this->import = $import;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'OpenAED Import error',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.import-error',
            with: [
                'import' => $this->import,
                'errorMessage' => $this->errorMessage,
                'recipient' => $this->to[0]['address'],
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