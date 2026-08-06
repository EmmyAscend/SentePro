<?php

namespace App\Services;

use App\Enums\PaymentProvider;
use App\Enums\PaymentTransactionStatus;
use App\Models\GatewayProvider;
use App\Models\PaymentLink;
use App\Models\PaymentTransaction;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PaymentInitiationService
{
    public function __construct(private readonly PaymentGatewayManager $gatewayManager) {}

    /**
     * @param  array{customer_name: string, customer_email: string, customer_phone?: ?string, currency: string, custom_fields?: array<string, ?string>}  $customer
     * @return array{transaction: PaymentTransaction, redirect_url: ?string}
     */
    public function initiate(PaymentLink $paymentLink, GatewayProvider $gatewayProvider, array $customer): array
    {
        if ($gatewayProvider->status !== 'active') {
            throw ValidationException::withMessages([
                'gateway_provider_id' => 'The selected payment method is not available for this payment link.',
            ]);
        }

        $currency = $customer['currency'];
        $supportedCurrencies = array_map('trim', explode(',', $gatewayProvider->supported_currencies));

        if (! in_array($currency, $supportedCurrencies, true)) {
            throw ValidationException::withMessages([
                'currency' => 'The selected payment method does not support '.$currency.'.',
            ]);
        }

        if ($gatewayProvider->provider === PaymentProvider::YoPayments && empty($customer['customer_phone'])) {
            throw ValidationException::withMessages([
                'customer_phone' => 'A phone number is required for mobile money payments.',
            ]);
        }

        $transaction = PaymentTransaction::create([
            'business_id' => $paymentLink->business_id,
            'payment_link_id' => $paymentLink->id,
            'provider' => $gatewayProvider->provider,
            'amount' => $paymentLink->amount,
            'currency' => $currency,
            'status' => PaymentTransactionStatus::Processing,
            'external_reference' => 'txn-'.Str::upper(Str::random(8)).'-'.$paymentLink->id,
            'customer_name' => $customer['customer_name'],
            'customer_email' => $customer['customer_email'],
            'customer_phone' => $customer['customer_phone'] ?? null,
            'custom_field_values' => $this->buildCustomFieldValues($paymentLink, $customer['custom_fields'] ?? []),
        ]);

        $driver = $this->gatewayManager->driver($gatewayProvider->provider);
        $result = $driver->initiate($transaction, $gatewayProvider);

        $transaction->update(['provider_reference' => $result['provider_reference']]);

        return ['transaction' => $transaction, 'redirect_url' => $result['redirect_url']];
    }

    /**
     * Attaches each submitted value to its field's label as configured on
     * the payment link *at submission time* — not a live join back to the
     * link's current `fields` config, so a later edit to the link's field
     * set doesn't rewrite history for transactions that already happened.
     *
     * @param  array<string, ?string>  $submitted
     * @return array<string, array{label: string, value: string}>
     */
    private function buildCustomFieldValues(PaymentLink $paymentLink, array $submitted): array
    {
        $values = [];

        foreach ($paymentLink->fields ?? [] as $field) {
            $value = trim((string) ($submitted[$field['key']] ?? ''));

            if ($value !== '') {
                $values[$field['key']] = ['label' => $field['label'], 'value' => $value];
            }
        }

        return $values;
    }
}
