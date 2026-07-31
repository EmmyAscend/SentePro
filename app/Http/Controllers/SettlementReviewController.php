<?php

namespace App\Http\Controllers;

use App\Models\Settlement;
use App\Services\SettlementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SettlementReviewController extends Controller
{
    public function __construct(private readonly SettlementService $settlementService) {}

    public function complete(Settlement $settlement): RedirectResponse
    {
        $this->authorize('process', $settlement);

        $this->settlementService->complete($settlement);

        return redirect()->route('settlements.index')->with('status', 'Settlement marked as completed.');
    }

    public function reject(Request $request, Settlement $settlement): RedirectResponse
    {
        $this->authorize('process', $settlement);

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->settlementService->reject($settlement, $validated['reason'] ?? null);

        return redirect()->route('settlements.index')->with('status', 'Settlement rejected.');
    }

    public function reverse(Request $request, Settlement $settlement): RedirectResponse
    {
        $this->authorize('process', $settlement);

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->settlementService->reverse($settlement, $validated['reason'] ?? null);

        return redirect()->route('settlements.index')->with('status', 'Settlement reversed.');
    }

    public function retry(Settlement $settlement): RedirectResponse
    {
        $this->authorize('process', $settlement);

        $this->settlementService->retry($settlement);

        return redirect()->route('settlements.index')->with('status', 'Settlement resubmitted for processing.');
    }
}
