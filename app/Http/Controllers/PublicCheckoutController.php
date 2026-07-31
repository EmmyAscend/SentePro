<?php

namespace App\Http\Controllers;

use App\Models\GatewayProvider;
use App\Models\PaymentLink;
use App\Models\PaymentTransaction;
use App\Services\PaymentInitiationService;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PublicCheckoutController extends Controller
{
    public function __construct(private readonly PaymentInitiationService $paymentInitiationService) {}

    public function show(PaymentLink $paymentLink): View
    {
        $gatewayProviders = GatewayProvider::query()
            ->where('business_id', $paymentLink->business_id)
            ->where('status', 'active')
            ->get();

        return view('checkout.show', compact('paymentLink', 'gatewayProviders'));
    }

    /**
     * Renders the payment link's checkout URL as a scannable SVG QR code —
     * same pattern as ReceiptController::qrCode(), pointed at the checkout
     * page instead of the receipt verification page.
     */
    public function qrCode(PaymentLink $paymentLink): Response
    {
        $result = Builder::create()
            ->writer(new SvgWriter)
            ->data(route('checkout.show', $paymentLink))
            ->size(240)
            ->margin(8)
            ->build();

        return response($result->getString(), 200, [
            'Content-Type' => $result->getMimeType(),
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    public function status(PaymentLink $paymentLink): View
    {
        $transaction = PaymentTransaction::where('payment_link_id', $paymentLink->id)
            ->latest()
            ->first();

        return view('checkout.status', compact('paymentLink', 'transaction'));
    }

    public function store(PaymentLink $paymentLink, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:20'],
            'currency' => ['required', 'string', 'max:10'],
            'gateway_provider_id' => ['required', 'exists:gateway_providers,id'],
        ]);

        $gatewayProvider = GatewayProvider::findOrFail($validated['gateway_provider_id']);

        $result = $this->paymentInitiationService->initiate($paymentLink, $gatewayProvider, $validated);

        if ($result['redirect_url']) {
            return redirect()->away($result['redirect_url']);
        }

        return redirect()->route('checkout.status', $paymentLink);
    }
}
