<?php

namespace App\Http\Controllers;

use App\Http\Requests\SettlementMethodRequest;
use App\Models\SettlementMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettlementMethodController extends Controller
{
    public function index(): View
    {
        $this->authorize('manage', SettlementMethod::class);

        $settlementMethods = SettlementMethod::query()->orderBy('name')->get();

        return view('admin.settlement-methods', compact('settlementMethods'));
    }

    public function store(SettlementMethodRequest $request): RedirectResponse
    {
        SettlementMethod::create($request->validated());

        return redirect()->route('admin.settlement-methods')->with('status', 'Settlement method created.');
    }

    public function update(SettlementMethodRequest $request, SettlementMethod $settlementMethod): RedirectResponse
    {
        $settlementMethod->update($request->validated());

        return redirect()->route('admin.settlement-methods')->with('status', 'Settlement method updated.');
    }
}
