<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSettlementRequest;
use App\Models\Business;
use App\Models\Settlement;
use App\Models\SettlementMethod;
use App\Services\SettlementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettlementController extends Controller
{
    public function __construct(private readonly SettlementService $settlementService) {}

    public function index(): View
    {
        $this->authorize('viewAny', Settlement::class);

        $user = auth()->user();

        $businesses = $user->isSuperAdmin()
            ? Business::with('wallet')->latest()->get()
            : Business::with('wallet')->whereKey($user->business_id)->get();

        $settlementMethods = SettlementMethod::query()->where('status', 'enabled')->orderBy('name')->get();

        // Settlement is tenant-scoped, so this is already limited to the current
        // user's business for anyone who isn't a super admin.
        $settlements = Settlement::with(['business', 'settlementMethod'])->latest()->get();

        return view('settlements.index', compact('businesses', 'settlementMethods', 'settlements'));
    }

    public function store(StoreSettlementRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $business = $request->user()->isSuperAdmin()
            ? Business::findOrFail($validated['business_id'])
            : Business::findOrFail($request->user()->business_id);

        $method = SettlementMethod::findOrFail($validated['settlement_method_id']);

        $this->settlementService->request($business, $method, $validated);

        return redirect()->route('settlements.index')->with('status', 'Settlement request submitted for review.');
    }
}
