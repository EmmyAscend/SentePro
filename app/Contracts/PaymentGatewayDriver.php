<?php

namespace App\Contracts;

use App\Models\GatewayProvider;
use App\Models\PaymentTransaction;

interface PaymentGatewayDriver
{
    /**
     * Initiate a payment with the gateway. `redirect_url` is null when the
     * gateway has no hosted checkout page to send the customer to (e.g. a
     * mobile money push prompt sent straight to their phone rather than a
     * browser redirect).
     *
     * @return array{redirect_url: ?string, provider_reference: string, raw: array}
     */
    public function initiate(PaymentTransaction $transaction, GatewayProvider $config): array;

    /**
     * Query the gateway's own authoritative status for a previously initiated
     * payment. This is the only source of truth for whether money actually
     * moved — never infer status from a webhook payload alone.
     *
     * @return array{status: 'processing'|'completed'|'failed', raw: array}
     */
    public function checkStatus(string $providerReference, GatewayProvider $config): array;

    /**
     * Refund a completed payment. Throws if the gateway has no refund API at all.
     *
     * @return array{status: 'completed'|'failed', raw: array}
     */
    public function refund(PaymentTransaction $transaction, float $amount, GatewayProvider $config): array;

    /**
     * A lightweight connectivity/credentials check — unlike the three methods
     * above, this never throws. It's designed for a "Test connection" button,
     * not a real operation, so a failure is a normal, expected outcome to
     * report rather than an exception to propagate.
     *
     * @return array{healthy: bool, error: ?string}
     */
    public function ping(GatewayProvider $config): array;
}
