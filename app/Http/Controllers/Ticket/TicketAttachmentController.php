<?php

namespace App\Http\Controllers\Ticket;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Serves ticket uploads through an authorised endpoint instead of the web
 * root (NFR-S24). Files live in storage with generated names (NFR-S25).
 */
class TicketAttachmentController extends Controller
{
    public function muatTurun(Request $request, Ticket $ticket, int $indeks): Response
    {
        $this->authorize('view', $ticket);

        $lampiran = $ticket->lampiran ?? [];

        if (! isset($lampiran[$indeks]['path'])) {
            throw new NotFoundHttpException('Lampiran tidak dijumpai.');
        }

        $fail = $lampiran[$indeks];

        return response()->download($fail['path'], $fail['name'] ?? 'lampiran');
    }
}
