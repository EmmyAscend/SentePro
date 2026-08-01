<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupportTicketMessageRequest;
use App\Models\SupportTicket;
use App\Services\SupportTicketService;
use Illuminate\Http\RedirectResponse;

class SupportTicketMessageController extends Controller
{
    public function __construct(private readonly SupportTicketService $supportTicketService) {}

    public function store(StoreSupportTicketMessageRequest $request, SupportTicket $ticket): RedirectResponse
    {
        $validated = $request->validated();

        $this->supportTicketService->reply($ticket, $request->user(), $validated['body']);

        return redirect()->route('support-tickets.show', $ticket)->with('status', 'Reply sent.');
    }
}
