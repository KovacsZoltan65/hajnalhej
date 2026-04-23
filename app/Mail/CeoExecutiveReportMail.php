<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CeoExecutiveReportMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @param array<string, mixed> $dashboard
     */
    public function __construct(
        public readonly array $dashboard,
    ) {
    }

    public function envelope(): Envelope
    {
        $date = now()->format('Y.m.d');

        return new Envelope(
            subject: "Hajnalhej CEO riport - {$date}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.ceo-executive-report',
        );
    }

    /**
     * @return array<int, string>
     */
    public function attachments(): array
    {
        return [];
    }
}
