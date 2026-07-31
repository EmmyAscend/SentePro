<?php

namespace App\Services\Gateways;

use App\Contracts\PaymentGatewayDriver;
use App\Models\GatewayProvider;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Pesapal API 3.0 (JSON REST). Pesapal's own docs state the IPN/callback
 * payload never carries payment status "for security reasons" and there is
 * no signature on it — GetTransactionStatus is the only authoritative source,
 * which is why checkStatus() is always called before a transaction is marked
 * completed (see PaymentWebhookService).
 */
class PesapalDriver implements PaymentGatewayDriver
{
    private function baseUrl(GatewayProvider $config): string
    {
        return $config->environment === 'production'
            ? config('services.pesapal.production_url')
            : config('services.pesapal.sandbox_url');
    }

    private function token(GatewayProvider $config): string
    {
        $credentials = $config->credentials ?? [];

        $response = Http::baseUrl($this->baseUrl($config))
            ->acceptJson()
            ->post('/api/Auth/RequestToken', [
                'consumer_key' => $credentials['consumer_key'] ?? null,
                'consumer_secret' => $credentials['consumer_secret'] ?? null,
            ])
            ->throw();

        return $response->json('token');
    }

    private function ipnId(GatewayProvider $config, string $token): string
    {
        $credentials = $config->credentials ?? [];

        if (! empty($credentials['ipn_id'])) {
            return $credentials['ipn_id'];
        }

        $response = Http::baseUrl($this->baseUrl($config))
            ->withToken($token)
            ->acceptJson()
            ->post('/api/URLSetup/RegisterIPN', [
                'url' => route('webhooks.pesapal.receive', $config),
                'ipn_notification_type' => 'POST',
            ])
            ->throw();

        $ipnId = $response->json('ipn_id');

        $config->update([
            'credentials' => [...$credentials, 'ipn_id' => $ipnId],
        ]);

        return $ipnId;
    }

    public function initiate(PaymentTransaction $transaction, GatewayProvider $config): array
    {
        $token = $this->token($config);
        $ipnId = $this->ipnId($config, $token);

        $nameParts = array_pad(explode(' ', trim((string) $transaction->customer_name), 2), 2, '');

        $paymentLink = $transaction->paymentLink;

        $response = Http::baseUrl($this->baseUrl($config))
            ->withToken($token)
            ->acceptJson()
            ->post('/api/Transactions/SubmitOrderRequest', [
                'id' => $transaction->external_reference,
                'currency' => $transaction->currency,
                'amount' => (float) $transaction->amount,
                'description' => mb_substr('Payment to '.$transaction->business->business_name, 0, 100),
                'callback_url' => $paymentLink ? route('checkout.status', $paymentLink) : config('app.url'),
                'notification_id' => $ipnId,
                'billing_address' => [
                    'email_address' => $transaction->customer_email,
                    'phone_number' => $transaction->customer_phone,
                    'first_name' => $nameParts[0],
                    'last_name' => $nameParts[1],
                ],
            ])
            ->throw();

        $data = $response->json();

        return [
            'redirect_url' => $data['redirect_url'] ?? null,
            'provider_reference' => $data['order_tracking_id'] ?? $transaction->external_reference,
            'raw' => $data,
        ];
    }

    public function checkStatus(string $providerReference, GatewayProvider $config): array
    {
        $token = $this->token($config);

        $response = Http::baseUrl($this->baseUrl($config))
            ->withToken($token)
            ->acceptJson()
            ->get('/api/Transactions/GetTransactionStatus', [
                'orderTrackingId' => $providerReference,
            ])
            ->throw();

        $data = $response->json();

        // Pesapal documents 0=INVALID, 1=COMPLETED, 2=FAILED, 3=REVERSED, and no
        // explicit "pending" code — anything unrecognized is treated as still
        // processing rather than guessed at.
        $status = match ((int) ($data['payment_status_code'] ?? -1)) {
            1 => 'completed',
            0, 2, 3 => 'failed',
            default => 'processing',
        };

        return ['status' => $status, 'raw' => $data];
    }

    public function refund(PaymentTransaction $transaction, float $amount, GatewayProvider $config): array
    {
        if (! $transaction->provider_reference) {
            throw new RuntimeException('Cannot refund a Pesapal transaction with no order tracking id on file.');
        }

        $token = $this->token($config);

        $status = $this->checkStatus($transaction->provider_reference, $config);
        $confirmationCode = $status['raw']['confirmation_code'] ?? null;

        if (! $confirmationCode) {
            throw new RuntimeException('Cannot refund a Pesapal transaction with no confirmation code on file — it may not have completed.');
        }

        $response = Http::baseUrl($this->baseUrl($config))
            ->withToken($token)
            ->acceptJson()
            ->post('/api/Transactions/RefundRequest', [
                'confirmation_code' => $confirmationCode,
                'amount' => $amount,
                'username' => $config->name,
                'remarks' => 'Refund requested via SentePro',
            ])
            ->throw();

        $data = $response->json();

        return [
            'status' => (string) ($data['status'] ?? '') === '200' ? 'completed' : 'failed',
            'raw' => $data,
        ];
    }
}
