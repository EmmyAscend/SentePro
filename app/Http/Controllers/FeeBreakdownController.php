<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFeeBreakdownRequest;
use App\Models\FeeBreakdown;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FeeBreakdownController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', FeeBreakdown::class);

        $feeBreakdowns = FeeBreakdown::with('business')->latest()->get();

        return view('fee-breakdowns.index', compact('feeBreakdowns'));
    }

    public function store(StoreFeeBreakdownRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $businessId = $request->user()->isSuperAdmin()
            ? $validated['business_id']
            : $request->user()->business_id;

        FeeBreakdown::create([...$validated, 'business_id' => $businessId]);

        return redirect()->route('fee-breakdowns.index')->with('status', 'Fee breakdown recorded successfully.');
    }
}
