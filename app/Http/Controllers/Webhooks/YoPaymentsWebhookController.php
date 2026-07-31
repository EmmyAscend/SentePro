<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\GatewayProvider;
use App\Models\PaymentTransaction;
use App\Services\PaymentWebhookService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class YoPaymentsWebhookController extends Controller
{
    public function __construct(private readonly PaymentWebhookService $webhookService) {}

    public function success(Request $request, GatewayProvider $gatewayProvider): Response
    {
        return $this->handle($request, $gatewayProvider, $request->input('external_ref'));
    }

    public function failure(Request $request, GatewayProvider $gatewayProvider): Response
    {
        return $this->handle($request, $gatewayProvider, $request->input('failed_transaction_reference'));
    }

    /**
     * Yo Payments' callbacks carry an RSA signature whose exact field order
     * isn't confidently confirmed against current documentation, so — same
     * principle as the Pesapal receiver — this handler doesn't trust the
     * payload's claimed outcome, it only uses it to find the transaction and
     * then reconciles via checkStatus(); see PaymentWebhookService.
     */
    private function handle(Request $request, GatewayProvider $gatewayProvider, ?string $externalReference): Response
    {
        $transaction = PaymentTransaction::query()
            ->where('business_id', $gatewayProvider->business_id)
            ->where('external_reference', $externalReference)
            ->first();

        if ($transaction) {
            $this->webhookService->reconcile($gatewayProvider, $transaction, $request->all());
        }

        return response('OK', 200);
    }
}
