<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentLinkRequest;
use App\Models\PaymentLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PaymentLinkController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', PaymentLink::class);

        $paymentLinks = PaymentLink::with('business')->latest()->get();

        return view('payment-links.index', compact('paymentLinks'));
    }

    public function store(StorePaymentLinkRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $businessId = $request->user()->isSuperAdmin()
            ? $validated['business_id']
            : $request->user()->business_id;

        PaymentLink::create([
            'business_id' => $businessId,
            'title' => $validated['title'],
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'custom_amount' => $validated['custom_amount'],
            'expiry_date' => $validated['expiry_date'],
            'description' => $validated['description'] ?? null,
            'status' => 'active',
        ]);

        return redirect()->route('payment-links.index')->with('status', 'Payment link created successfully.');
    }
}
