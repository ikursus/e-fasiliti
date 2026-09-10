<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

/**
 * In-app ticket notification (FR-NOT-02). Deliberately not queued: the
 * database channel insert must land even when no queue worker is running,
 * because in-app alerts are the fallback channel when e-mail fails.
 */
class TicketEventNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $peristiwa,
        private readonly string $tajuk,
        private readonly string $pesan,
        private readonly string $pautan,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): DatabaseMessage
    {
        return new DatabaseMessage([
            'peristiwa' => $this->peristiwa,
            'tajuk' => $this->tajuk,
            'pesan' => $this->pesan,
            'pautan' => $this->pautan,
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'peristiwa' => $this->peristiwa,
            'tajuk' => $this->tajuk,
            'pesan' => $this->pesan,
            'pautan' => $this->pautan,
        ];
    }
}
