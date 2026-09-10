<?php

namespace App\Mail;

use App\Models\NotificationTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Renders a NotificationTemplate (M01/FR-ADM-06) with context placeholders.
 * Queued so a slow SMTP server never blocks the web request; the caller
 * catches transport failures, because e-mail must never break the
 * transaction it announces (NFR-A05).
 */
class TicketEventMail extends Mailable implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly NotificationTemplate $template,
        private readonly array $data,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->render($this->template->subject),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.ticket-event',
            with: ['kandungan' => $this->render($this->template->body)],
        );
    }

    /**
     * Replace every {{ placeholder }} the template declares with its context
     * value; unknown placeholders degrade to an empty string rather than
     * leaking template syntax to the recipient.
     */
    private function render(string $text): string
    {
        return preg_replace_callback(
            '/\{\{\s*([a-z0-9_]+)\s*\}\}/i',
            fn (array $match): string => (string) ($this->data[$match[1]] ?? ''),
            $text,
        ) ?? $text;
    }
}
