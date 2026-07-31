<?php

namespace App\Services;

use App\Models\GatewayProvider;
use App\Models\PaymentTransaction;
use App\Models\WebhookEvent;

class PaymentWebhookService
{
    public function __construct(private readonly PaymentGatewayManager $gatewayManager) {}

    /**
     * Reconcile a transaction against the gateway's own authoritative status.
     * Never trusts the inbound webhook payload's claimed status directly —
     * a webhook is only ever a trigger to go check, not an assertion to act on.
     * This is what covers both Pesapal's by-design lack of webhook status and
     * Yo Payments' unconfirmed signature scheme without depending on either.
     */
    public function reconcile(GatewayProvider $config, PaymentTransaction $transaction, array $rawPayload): PaymentTransaction
    {
        $driver = $this->gatewayManager->driver($config->provider);

        $result = $driver->checkStatus($transaction->provider_reference, $config);

        if ($transaction->status->value !== $result['status']) {
            $transaction->update(['status' => $result['status']]);
        }

        WebhookEvent::create([
            'business_id' => $config->business_id,
            'provider' => $config->provider->value,
            'event' => 'payment.status_check',
            'payload' => json_encode(['inbound' => $rawPayload, 'status_check' => $result['raw']]),
        ]);

        return $transaction->fresh();
    }
}
