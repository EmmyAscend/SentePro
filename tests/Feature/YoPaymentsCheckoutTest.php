<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\GatewayProvider;
use App\Models\PaymentLink;
use App\Models\PaymentTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class YoPaymentsCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function gatewayProvider(): GatewayProvider
    {
        return GatewayProvider::create([
            'provider' => 'yo_payments',
            'status' => 'active',
            'environment' => 'sandbox',
            'webhook_url' => 'https://example.test/webhooks/yo-payments/success',
            'credentials' => ['api_username' => 'test-user', 'api_password' => 'test-pass'],
            'supported_currencies' => 'UGX',
        ]);
    }

    private function xmlResponse(array $fields): string
    {
        $body = '<Response>';
        foreach ($fields as $key => $value) {
            $body .= "<{$key}>{$value}</{$key}>";
        }
        $body .= '</Response>';

        return '<?xml version="1.0" encoding="UTF-8"?><AutoCreate>'.$body.'</AutoCreate>';
    }

    public function test_checkout_requires_a_phone_number_for_mobile_money(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $this->gatewayProvider();
        $paymentLink = PaymentLink::factory()->create(['business_id' => $business->id, 'amount' => 5000]);

        $response = $this->post('/pay/'.$paymentLink->id, [
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.test',
            'currency' => 'UGX',
            'payment_method' => 'mobile_money',
        ]);

        $response->assertSessionHasErrors('customer_phone');
        $this->assertDatabaseMissing('payment_transactions', ['business_id' => $business->id]);
    }

    public function test_checkout_pushes_a_mobile_money_prompt_and_lands_on_the_status_page(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $gatewayProvider = $this->gatewayProvider();
        $paymentLink = PaymentLink::factory()->create(['business_id' => $business->id, 'amount' => 5000]);

        Http::fake([
            '*' => Http::response($this->xmlResponse([
                'Status' => 'OK',
                'StatusCode' => 0,
                'TransactionStatus' => 'PENDING',
                'TransactionReference' => 'yo-txn-ref-123',
            ]), 200, ['Content-Type' => 'text/xml']),
        ]);

        $response = $this->post('/pay/'.$paymentLink->id, [
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.test',
            'customer_phone' => '256712345678',
            'currency' => 'UGX',
            'payment_method' => 'mobile_money',
        ]);

        // No hosted checkout page for mobile money — the prompt goes straight
        // to the customer's phone, so this redirects to our own status page.
        $response->assertRedirect('/pay/'.$paymentLink->id.'/status');

        $this->assertDatabaseHas('payment_transactions', [
            'business_id' => $business->id,
            'payment_link_id' => $paymentLink->id,
            'provider' => 'yo_payments',
            'status' => 'processing',
            'provider_reference' => 'yo-txn-ref-123',
            'customer_phone' => '256712345678',
        ]);

        Http::assertSent(function ($request) {
            return str_contains($request->body(), '<Method>acdepositfunds</Method>')
                && str_contains($request->body(), '<Account>256712345678</Account>');
        });

        $this->assertDatabaseHas('gateway_logs', [
            'gateway_provider_id' => $gatewayProvider->id,
            'method' => 'initiate',
            'success' => 1,
        ]);
        $this->assertSame('healthy', $gatewayProvider->fresh()->last_health_status);
    }

    /**
     * The actual reported bug: a customer typing a local-format number
     * (0712345678, exactly how most Ugandan customers naturally type their
     * own number) produced no mobile money prompt at all. Yo Payments'
     * Account field requires strict international format with no leading
     * zero — sending the local format unnormalized meant the request never
     * mapped to a real subscriber, which looks identical from the outside
     * to "nothing happened." PhoneNumber::normalizeUganda() fixes this at
     * the point YoPaymentsDriver builds the request; the stored
     * customer_phone column is left exactly as the customer typed it.
     */
    public function test_a_locally_formatted_phone_number_is_normalized_before_reaching_yo_payments(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $this->gatewayProvider();
        $paymentLink = PaymentLink::factory()->create(['business_id' => $business->id, 'amount' => 5000]);

        Http::fake([
            '*' => Http::response($this->xmlResponse([
                'Status' => 'OK',
                'StatusCode' => 0,
                'TransactionStatus' => 'PENDING',
                'TransactionReference' => 'yo-txn-ref-local',
            ]), 200, ['Content-Type' => 'text/xml']),
        ]);

        $this->post('/pay/'.$paymentLink->id, [
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.test',
            'customer_phone' => '0712345678',
            'currency' => 'UGX',
            'payment_method' => 'mobile_money',
        ])->assertRedirect('/pay/'.$paymentLink->id.'/status');

        // The customer's own typed format is preserved for display/audit...
        $this->assertDatabaseHas('payment_transactions', [
            'payment_link_id' => $paymentLink->id,
            'customer_phone' => '0712345678',
        ]);

        // ...but Yo Payments only ever sees the normalized international form.
        Http::assertSent(function ($request) {
            return str_contains($request->body(), '<Method>acdepositfunds</Method>')
                && str_contains($request->body(), '<Account>256712345678</Account>')
                && ! str_contains($request->body(), '<Account>0712345678</Account>');
        });
    }

    public function test_yo_payments_webhook_reconciles_status_and_credits_the_wallet_on_completion(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $this->gatewayProvider();
        $paymentLink = PaymentLink::factory()->create(['business_id' => $business->id, 'amount' => 5000]);

        $transaction = PaymentTransaction::create([
            'business_id' => $business->id,
            'payment_link_id' => $paymentLink->id,
            'provider' => 'yo_payments',
            'amount' => 5000,
            'currency' => 'UGX',
            'status' => 'processing',
            'external_reference' => 'txn-ref-yo-1',
            'provider_reference' => 'yo-txn-ref-123',
            'customer_phone' => '256712345678',
        ]);

        Http::fake([
            '*' => Http::response($this->xmlResponse([
                'Status' => 'OK',
                'StatusCode' => 0,
                'TransactionStatus' => 'SUCCEEDED',
                'TransactionReference' => 'yo-txn-ref-123',
            ]), 200, ['Content-Type' => 'text/xml']),
        ]);

        // Yo's callback signature scheme isn't confidently verifiable (see
        // YoPaymentsWebhookController docblock), so this only ever finds the
        // transaction from the payload — the completed/failed determination
        // still comes from actransactioncheckstatus, not the payload itself.
        $response = $this->post('/webhooks/yo-payments/success', [
            'external_ref' => 'txn-ref-yo-1',
            'network_ref' => 'yo-txn-ref-123',
            'amount' => 5000,
        ]);

        $response->assertOk();
        $this->assertSame('completed', $transaction->fresh()->status->value);

        $wallet = $business->wallet->fresh();
        $this->assertSame('5000.00', (string) $wallet->available_balance);
        $this->assertSame('5000.00', (string) $wallet->settlement_balance);
    }

    public function test_yo_payments_failure_callback_does_not_credit_the_wallet_when_status_check_says_failed(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $this->gatewayProvider();
        $paymentLink = PaymentLink::factory()->create(['business_id' => $business->id, 'amount' => 5000]);

        $transaction = PaymentTransaction::create([
            'business_id' => $business->id,
            'payment_link_id' => $paymentLink->id,
            'provider' => 'yo_payments',
            'amount' => 5000,
            'currency' => 'UGX',
            'status' => 'processing',
            'external_reference' => 'txn-ref-yo-2',
            'provider_reference' => 'yo-txn-ref-999',
        ]);

        Http::fake([
            '*' => Http::response($this->xmlResponse([
                'Status' => 'OK',
                'StatusCode' => 0,
                'TransactionStatus' => 'FAILED',
                'TransactionReference' => 'yo-txn-ref-999',
            ]), 200, ['Content-Type' => 'text/xml']),
        ]);

        $this->post('/webhooks/yo-payments/failure', [
            'failed_transaction_reference' => 'txn-ref-yo-2',
        ])->assertOk();

        $this->assertSame('failed', $transaction->fresh()->status->value);
        $this->assertSame('0.00', (string) $business->wallet->fresh()->available_balance);
    }
}
