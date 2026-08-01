<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Services\SupportTicketService;
use Illuminate\Http\RedirectResponse;

class SupportTicketReviewController extends Controller
{
    public function __construct(private readonly SupportTicketService $supportTicketService) {}

    public function resolve(SupportTicket $ticket): RedirectResponse
    {
        $this->authorize('update', $ticket);

        $this->supportTicketService->resolve($ticket);

        return redirect()->route('support-tickets.show', $ticket)->with('status', 'Ticket marked as resolved.');
    }

    public function reopen(SupportTicket $ticket): RedirectResponse
    {
        $this->authorize('update', $ticket);

        $this->supportTicketService->reopen($ticket);

        return redirect()->route('support-tickets.show', $ticket)->with('status', 'Ticket reopened.');
    }
}
