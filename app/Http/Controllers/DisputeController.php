<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDisputeRequest;
use App\Models\Dispute;
use App\Models\PaymentTransaction;
use App\Services\DisputeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DisputeController extends Controller
{
    public function __construct(private readonly DisputeService $disputeService) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Dispute::class);

        $user = $request->user();

        $disputes = Dispute::query()
            ->with(['business', 'paymentTransaction'])
            ->when(! $user->isSuperAdmin(), fn ($query) => $query->where('business_id', $user->business_id))
            ->latest()
            ->get();

        return view('disputes.index', compact('disputes'));
    }

    public function show(Dispute $dispute): View
    {
        $this->authorize('view', $dispute);

        $dispute->load(['paymentTransaction', 'business', 'raisedBy', 'resolvedBy']);

        return view('disputes.show', compact('dispute'));
    }

    public function store(StoreDisputeRequest $request, PaymentTransaction $transaction): RedirectResponse
    {
        $validated = $request->validated();

        $dispute = $this->disputeService->open(
            $transaction,
            $request->user(),
            $validated['reason'],
            $validated['description'] ?? null,
        );

        return redirect()->route('disputes.show', $dispute)->with('status', 'Dispute raised successfully.');
    }
}
