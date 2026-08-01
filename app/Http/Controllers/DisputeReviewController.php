<?php

namespace App\Http\Controllers;

use App\Models\Dispute;
use App\Services\DisputeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DisputeReviewController extends Controller
{
    public function __construct(private readonly DisputeService $disputeService) {}

    public function resolve(Request $request, Dispute $dispute): RedirectResponse
    {
        $this->authorize('process', $dispute);

        $validated = $request->validate([
            'notes' => ['required', 'string', 'max:2000'],
        ]);

        $this->disputeService->resolve($dispute, $request->user(), $validated['notes']);

        return redirect()->route('disputes.show', $dispute)->with('status', 'Dispute resolved.');
    }

    public function reject(Request $request, Dispute $dispute): RedirectResponse
    {
        $this->authorize('process', $dispute);

        $validated = $request->validate([
            'notes' => ['required', 'string', 'max:2000'],
        ]);

        $this->disputeService->reject($dispute, $request->user(), $validated['notes']);

        return redirect()->route('disputes.show', $dispute)->with('status', 'Dispute rejected.');
    }
}
