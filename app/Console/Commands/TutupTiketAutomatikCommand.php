<?php

namespace App\Console\Commands;

use App\Services\Ticket\TicketService;
use Illuminate\Console\Command;

/**
 * FR-TKT-18: auto-close tickets the reporter never confirmed, after three
 * working days. Safe to run repeatedly (NFR-A07) — it only touches tickets
 * still in menunggu_pengesahan.
 */
class TutupTiketAutomatikCommand extends Command
{
    protected $signature = 'tiket:tutup-automatik';

    protected $description = 'Tutup tiket yang tidak disahkan pelapor selepas tiga hari bekerja';

    public function handle(TicketService $tickets): int
    {
        $tutup = $tickets->tutupAutomatik();

        $this->info("{$tutup} tiket ditutup secara automatik.");

        return self::SUCCESS;
    }
}
