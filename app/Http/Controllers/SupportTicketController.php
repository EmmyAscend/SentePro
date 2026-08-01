<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupportTicketRequest;
use App\Models\SupportTicket;
use App\Services\SupportTicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    public function __construct(private readonly SupportTicketService $supportTicketService) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', SupportTicket::class);

        $user = $request->user();

        $tickets = SupportTicket::query()
            ->with('business')
            ->when(! $user->isSuperAdmin(), fn ($query) => $query->where('business_id', $user->business_id))
            ->latest()
            ->get();

        return view('support-tickets.index', compact('tickets'));
    }

    public function show(SupportTicket $ticket): View
    {
        $this->authorize('view', $ticket);

        $ticket->load(['messages.user', 'business']);

        return view('support-tickets.show', compact('ticket'));
    }

    public function store(StoreSupportTicketRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $ticket = $this->supportTicketService->open(
            $request->user()->business,
            $request->user(),
            $validated['subject'],
            $validated['body'],
        );

        return redirect()->route('support-tickets.show', $ticket)->with('status', 'Support ticket opened.');
    }
}
