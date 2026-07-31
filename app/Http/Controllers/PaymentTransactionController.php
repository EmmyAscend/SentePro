<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentTransactionRequest;
use App\Models\PaymentTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PaymentTransactionController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', PaymentTransaction::class);

        $transactions = PaymentTransaction::with('business')->latest()->get();

        return view('transactions.index', compact('transactions'));
    }

    public function store(StorePaymentTransactionRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $businessId = $request->user()->isSuperAdmin()
            ? $validated['business_id']
            : $request->user()->business_id;

        PaymentTransaction::create([...$validated, 'business_id' => $businessId]);

        return redirect()->route('transactions.index')->with('status', 'Transaction recorded successfully.');
    }
}
