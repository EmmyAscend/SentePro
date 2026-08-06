<?php

namespace App\Http\Controllers\Webhooks;

use App\Enums\PaymentProvider;
use App\Http\Controllers\Controller;
use App\Models\GatewayProvider;
use App\Models\PaymentTransaction;
use App\Services\PaymentWebhookService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class YoPaymentsWebhookController extends Controller
{
    public function __construct(private readonly PaymentWebhookService $webhookService) {}

    public function success(Request $request): Response
    {
        return $this->handle($request, $request->input('external_ref'));
    }

    public function failure(Request $request): Response
    {
        return $this->handle($request, $request->input('failed_transaction_reference'));
    }

    /**
     * Yo Payments' callbacks carry an RSA signature whose exact field order
     * isn't confidently confirmed against current documentation, so — same
     * principle as the Pesapal receiver — this handler doesn't trust the
     * payload's claimed outcome, it only uses it to find the transaction and
     * then reconciles via checkStatus(); see PaymentWebhookService. There is
     * now exactly one platform-wide Yo Payments GatewayProvider row, so no
     * route parameter is needed to identify it.
     */
    private function handle(Request $request, ?string $externalReference): Response
    {
        $gatewayProvider = GatewayProvider::byProvider(PaymentProvider::YoPayments);

        $transaction = PaymentTransaction::query()
            ->where('external_reference', $externalReference)
            ->first();

        if ($transaction) {
            $this->webhookService->reconcile($gatewayProvider, $transaction, $request->all());
        }

        return response('OK', 200);
    }
}
