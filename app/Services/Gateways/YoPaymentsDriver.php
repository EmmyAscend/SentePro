<?php

namespace App\Services\Gateways;

use App\Contracts\PaymentGatewayDriver;
use App\Models\GatewayProvider;
use App\Models\PaymentTransaction;
use App\Support\PhoneNumber;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use SimpleXMLElement;

/**
 * Yo Payments' "AC" protocol: a single task.php endpoint, XML request/response
 * bodies, method-dispatched (not a modern JSON REST API — verified this is
 * still current, not legacy). Network (MTN vs Airtel) is auto-detected from
 * the phone number prefix; there is no separate code path per network.
 */
class YoPaymentsDriver implements PaymentGatewayDriver
{
    private function baseUrl(GatewayProvider $config): string
    {
        return $config->environment === 'production'
            ? config('services.yo_payments.production_url')
            : config('services.yo_payments.sandbox_url');
    }

    private function buildXml(string $method, array $fields, GatewayProvider $config): string
    {
        $credentials = $config->credentials ?? [];

        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><AutoCreate><Request/></AutoCreate>');
        $request = $xml->Request;
        $request->addChild('APIUsername', $credentials['api_username'] ?? '');
        $request->addChild('APIPassword', $credentials['api_password'] ?? '');
        $request->addChild('Method', $method);

        foreach ($fields as $key => $value) {
            if ($value !== null && $value !== '') {
                $request->addChild($key, (string) $value);
            }
        }

        return $xml->asXML();
    }

    private function send(string $method, array $fields, GatewayProvider $config): array
    {
        $response = Http::withBody($this->buildXml($method, $fields, $config), 'text/xml')
            ->post($this->baseUrl($config))
            ->throw();

        $previous = libxml_use_internal_errors(true);
        $parsed = simplexml_load_string($response->body());
        libxml_use_internal_errors($previous);

        if ($parsed === false) {
            throw new RuntimeException('Yo Payments returned an unparseable response.');
        }

        $data = json_decode(json_encode($parsed), true) ?? [];

        // Response fields may come back wrapped in a <Response> element
        // (mirroring the <Request> envelope) or flat at the root — the exact
        // shape isn't confirmed against the primary spec (see YoPaymentsDriver
        // class docblock), so both are handled.
        return $data['Response'] ?? $data;
    }

    public function initiate(PaymentTransaction $transaction, GatewayProvider $config): array
    {
        if (! $transaction->customer_phone) {
            throw new RuntimeException('A customer phone number is required to initiate a Yo Payments collection.');
        }

        $data = $this->send('acdepositfunds', [
            'Amount' => (float) $transaction->amount,
            'Account' => PhoneNumber::normalizeUganda($transaction->customer_phone),
            'Narrative' => mb_substr('Payment to '.$transaction->business->business_name, 0, 100),
            'ExternalReference' => $transaction->external_reference,
            'NonBlocking' => 'TRUE',
            'InstantNotificationUrl' => route('webhooks.yo-payments.success'),
            'FailureNotificationUrl' => route('webhooks.yo-payments.failure'),
        ], $config);

        return [
            'redirect_url' => null,
            'provider_reference' => $data['TransactionReference'] ?? $transaction->external_reference,
            'raw' => $data,
        ];
    }

    public function checkStatus(string $providerReference, GatewayProvider $config): array
    {
        $data = $this->send('actransactioncheckstatus', [
            'TransactionReference' => $providerReference,
            'DepositTransactionType' => 'PUSH',
        ], $config);

        $status = match ($data['TransactionStatus'] ?? null) {
            'SUCCEEDED' => 'completed',
            'FAILED' => 'failed',
            default => 'processing', // PENDING / INDETERMINATE / unrecognized
        };

        return ['status' => $status, 'raw' => $data];
    }

    public function refund(PaymentTransaction $transaction, float $amount, GatewayProvider $config): array
    {
        throw new RuntimeException('Yo Payments has no refund/reversal API — issue a manual disbursement instead.');
    }

    /**
     * Yo Payments has no separate auth step — credentials travel with every
     * request — so there's no true "ping" endpoint. A round-trip against a
     * nonsense reference that completes without throwing is the best signal
     * available that connectivity/credentials are fine; it can't fully rule
     * out "bad credentials that still return 200," the same unconfirmed-
     * response-shape caveat already flagged for this driver.
     */
    public function ping(GatewayProvider $config): array
    {
        try {
            $this->send('actransactioncheckstatus', [
                'TransactionReference' => 'sentepro-health-check',
                'DepositTransactionType' => 'PUSH',
            ], $config);

            return ['healthy' => true, 'error' => null];
        } catch (\Throwable $e) {
            return ['healthy' => false, 'error' => $e->getMessage()];
        }
    }
}
