<?php

namespace App\Services\Ticket;

use App\Models\Ticket;

/**
 * Business reference numbers TKT-YYYYMM-nnnnn (DRD §4.3). The sequence is
 * derived per month, so a race between two simultaneous submissions is
 * resolved by the unique index plus the retry loop in TicketService::buka().
 */
class TicketNumberGenerator
{
    public function next(): string
    {
        $prefix = 'TKT-'.now()->format('Ym').'-';

        $maxTail = (int) substr(
            (string) Ticket::query()
                ->where('no_tiket', 'like', $prefix.'%')
                ->max('no_tiket'),
            -5,
        );

        return $prefix.str_pad((string) ($maxTail + 1), 5, '0', STR_PAD_LEFT);
    }
}
