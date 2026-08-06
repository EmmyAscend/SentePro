<?php

namespace App\Http\Controllers\Webhooks;

use App\Enums\PaymentProvider;
use App\Http\Controllers\Controller;
use App\Models\GatewayProvider;
use App\Models\PaymentTransaction;
use App\Services\PaymentWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PesapalWebhookController extends Controller
{
    public function __construct(private readonly PaymentWebhookService $webhookService) {}

    /**
     * Pesapal's IPN deliberately never carries the payment status — this
     * handler's only job is to look up the transaction and trigger a
     * checkStatus() reconciliation; see PaymentWebhookService. There is now
     * exactly one platform-wide Pesapal GatewayProvider row, so no route
     * parameter is needed to identify it.
     */
    public function receive(Request $request): JsonResponse
    {
        $gatewayProvider = GatewayProvider::byProvider(PaymentProvider::Pesapal);

        $orderTrackingId = $request->input('OrderTrackingId');
        $merchantReference = $request->input('OrderMerchantReference');
        $notificationType = $request->input('OrderNotificationType', 'IPNCHANGE');

        $transaction = PaymentTransaction::query()
            ->where(function ($query) use ($orderTrackingId, $merchantReference) {
                $query->where('provider_reference', $orderTrackingId)
                    ->orWhere('external_reference', $merchantReference);
            })
            ->first();

        if ($transaction) {
            $this->webhookService->reconcile($gatewayProvider, $transaction, $request->all());
        }

        return response()->json([
            'orderNotificationType' => $notificationType,
            'orderTrackingId' => $orderTrackingId,
            'orderMerchantReference' => $merchantReference,
            'status' => 200,
        ]);
    }
}
