<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Import;

class ImportSuccess extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The import instance.
     */
    protected Import $import;

    /**
     * Create a new message instance.
     */
    public function __construct(Import $import)
    {
        $this->import = $import;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'OpenAED Import finished',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.import-success',
            with: [
                'import' => $this->import,
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