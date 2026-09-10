<?php

namespace App\Console\Commands;

use App\Services\Ticket\TicketService;
use Illuminate\Console\Command;

/**
 * FR-TKT-20: sweep open tickets at the 80% and 100% SLA thresholds and
 * alert the penyelia and the assigned juruteknik (UC-18). Idempotent: each
 * ticket stores when its alerts were sent, so a rerun sends nothing twice.
 */
class AmaranSlaTiketCommand extends Command
{
    protected $signature = 'tiket:amaran-sla';

    protected $description = 'Hantar amaran SLA 80% dan pelanggaran 100% kepada penyelia dan juruteknik';

    public function handle(TicketService $tickets): int
    {
        $hasil = $tickets->semakAmaranSla();

        $this->info("Amaran 80%: {$hasil['amar_80']}. Amaran pelanggaran: {$hasil['amar_100']}.");

        return self::SUCCESS;
    }
}
