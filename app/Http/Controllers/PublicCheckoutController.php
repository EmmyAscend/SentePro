<?php

namespace App\Http\Controllers;

use App\Enums\PaymentProvider;
use App\Enums\PaymentTransactionStatus;
use App\Models\GatewayProvider;
use App\Models\PaymentLink;
use App\Models\PaymentTransaction;
use App\Services\PaymentInitiationService;
use App\Services\PaymentWebhookService;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Throwable;

class PublicCheckoutController extends Controller
{
    /**
     * The only two payment methods a customer ever sees — never the
     * provider name behind them. Card always routes to Pesapal, mobile
     * money always routes to Yo Payments; this mapping is a fixed platform
     * rule, not admin-configurable.
     */
    private const METHOD_PROVIDERS = [
        'card' => PaymentProvider::Pesapal,
        'mobile_money' => PaymentProvider::YoPayments,
    ];

    public function __construct(private readonly PaymentInitiationService $paymentInitiationService) {}

    public function show(PaymentLink $paymentLink): View
    {
        $cardProvider = GatewayProvider::byProvider(PaymentProvider::Pesapal);
        $mobileMoneyProvider = GatewayProvider::byProvider(PaymentProvider::YoPayments);

        return view('checkout.show', compact('paymentLink', 'cardProvider', 'mobileMoneyProvider'));
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

    /**
     * Polled by checkout/status.blade.php while a transaction is still
     * processing. Actively re-checks the gateway's own status rather than
     * just reading our possibly-stale DB row — the same "a webhook is only
     * ever a trigger to go check" principle PaymentWebhookService already
     * documents, and exactly the periodic-check pattern Yo Payments' own
     * integration docs recommend for a pending transaction. A failed
     * gateway call (bad credentials, network error) is swallowed — the
     * poll just reports the last-known status rather than breaking the
     * page; LoggingGatewayDriver already records the failure separately.
     */
    public function statusCheck(PaymentLink $paymentLink, PaymentWebhookService $webhookService): JsonResponse
    {
        $transaction = PaymentTransaction::where('payment_link_id', $paymentLink->id)
            ->latest()
            ->first();

        if (! $transaction) {
            return response()->json(['status' => 'not_found']);
        }

        if ($transaction->status === PaymentTransactionStatus::Processing) {
            try {
                $gatewayProvider = GatewayProvider::byProvider($transaction->provider);
                $transaction = $webhookService->reconcile($gatewayProvider, $transaction, []);
            } catch (Throwable) {
                // Keep serving the last-known status below.
            }
        }

        return response()->json([
            'status' => $transaction->status->value,
            'receipt_url' => $transaction->receipt ? route('receipts.show', $transaction->receipt) : null,
        ]);
    }

    public function store(PaymentLink $paymentLink, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:20'],
            'currency' => ['required', 'string', 'max:10'],
            'payment_method' => ['required', 'string', 'in:card,mobile_money'],
            'custom_fields' => ['nullable', 'array'],
            'custom_fields.*' => ['nullable', 'string', 'max:500'],
        ]);

        $gatewayProvider = GatewayProvider::byProvider(self::METHOD_PROVIDERS[$validated['payment_method']]);

        $result = $this->paymentInitiationService->initiate($paymentLink, $gatewayProvider, $validated);

        if ($result['redirect_url']) {
            return redirect()->away($result['redirect_url']);
        }

        return redirect()->route('checkout.status', $paymentLink);
    }
}
