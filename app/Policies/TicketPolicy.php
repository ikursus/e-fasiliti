<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

/**
 * Server-side authorisation for every ticket record (FR-USR-05, NFR-S11).
 * Hiding buttons in the views is never treated as a control.
 */
class TicketPolicy
{
    /**
     * The reporter, the assigned technician, and anyone holding
     * tiket.lihat-semua may read a ticket.
     */
    public function view(User $user, Ticket $ticket): bool
    {
        return $ticket->pelapor_id === $user->id
            || $ticket->juruteknik_id === $user->id
            || $user->can('tiket.lihat-semua');
    }

    /**
     * Only the reporter may confirm closure or reopen (FR-TKT-17/19).
     */
    public function reporterActions(User $user, Ticket $ticket): bool
    {
        return $ticket->pelapor_id === $user->id;
    }

    /**
     * Technician work: the assigned technician, or anyone with the update
     * permission (penyelia, pentadbir) acting on the technician's behalf.
     */
    public function work(User $user, Ticket $ticket): bool
    {
        return $ticket->juruteknik_id === $user->id || $user->can('tiket.kemas-kini');
    }
}
