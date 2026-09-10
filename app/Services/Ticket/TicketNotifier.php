<?php

namespace App\Services\Ticket;

use App\Mail\TicketEventMail;
use App\Models\NotificationTemplate;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketEventNotification;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Single doorway for M10 outbound messages (SDD §5.4): the in-app channel is
 * written synchronously so alerts survive without a queue worker, and the
 * e-mail channel is queued from a NotificationTemplate and treated as
 * best-effort — a mail failure never fails the business transaction
 * (NFR-A05, UC-01 exception 11a).
 */
class TicketNotifier
{
    /**
     * @param  array<string, string>  $data  Template placeholder values.
     */
    public function hantar(User $penerima, Ticket $tiket, string $eventKey, string $tajuk, string $pesan, array $data = []): void
    {
        $penerima->notify(new TicketEventNotification(
            $eventKey,
            $tajuk,
            $pesan,
            route('tiket.show', $tiket),
        ));

        $this->hantarEmel($penerima, $eventKey, $data);
    }

    private function hantarEmel(User $penerima, string $eventKey, array $data): void
    {
        try {
            $template = NotificationTemplate::query()
                ->active()
                ->where('key', $eventKey)
                ->where('channel', 'emel')
                ->where('locale', $penerima->bahasa_pilihan ?? 'ms')
                ->first();

            if ($template === null || $penerima->email === null) {
                return;
            }

            Mail::to($penerima->email)->queue(new TicketEventMail($template, $data));
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
