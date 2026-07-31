<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentLinkRequest;
use App\Models\Business;
use App\Models\PaymentLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentLinkController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', PaymentLink::class);

        $user = $request->user();

        $businesses = $user->isSuperAdmin()
            ? Business::query()->orderBy('business_name')->get()
            : Business::query()->whereKey($user->business_id)->get();

        $paymentLinks = PaymentLink::with('business')->latest()->get();

        return view('payment-links.index', compact('paymentLinks', 'businesses'));
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
