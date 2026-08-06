<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\GatewayProvider;
use App\Models\PaymentLink;
use App\Models\PaymentTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PesapalCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function gatewayProvider(): GatewayProvider
    {
        return GatewayProvider::create([
            'provider' => 'pesapal',
            'status' => 'active',
            'environment' => 'sandbox',
            'webhook_url' => 'https://example.test/webhooks/pesapal',
            'credentials' => ['consumer_key' => 'test-key', 'consumer_secret' => 'test-secret'],
            'supported_currencies' => 'UGX',
        ]);
    }

    public function test_checkout_initiates_a_pesapal_order_and_redirects_to_the_hosted_checkout_page(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $gatewayProvider = $this->gatewayProvider();
        $paymentLink = PaymentLink::factory()->create([
            'business_id' => $business->id,
            'amount' => 2500,
        ]);

        Http::fake([
            '*/api/Auth/RequestToken' => Http::response(['token' => 'fake-token', 'status' => '200']),
            '*/api/URLSetup/RegisterIPN' => Http::response(['ipn_id' => 'ipn-guid-123', 'status' => '200']),
            '*/api/Transactions/SubmitOrderRequest' => Http::response([
                'order_tracking_id' => 'tracking-abc-123',
                'merchant_reference' => 'merchant-ref',
                'redirect_url' => 'https://cybqa.pesapal.com/pesapalv3/checkout/abc123',
                'status' => '200',
            ]),
        ]);

        $response = $this->post('/pay/'.$paymentLink->id, [
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.test',
            'currency' => 'UGX',
            'payment_method' => 'card',
        ]);

        $response->assertRedirect('https://cybqa.pesapal.com/pesapalv3/checkout/abc123');

        $this->assertDatabaseHas('payment_transactions', [
            'business_id' => $business->id,
            'payment_link_id' => $paymentLink->id,
            'provider' => 'pesapal',
            'status' => 'processing',
            'provider_reference' => 'tracking-abc-123',
        ]);

        // the ipn_id Pesapal returned gets cached on the gateway config so it
        // isn't re-registered on every checkout
        $this->assertSame('ipn-guid-123', $gatewayProvider->fresh()->credentials['ipn_id']);

        $this->assertDatabaseHas('gateway_logs', [
            'gateway_provider_id' => $gatewayProvider->id,
            'method' => 'initiate',
            'success' => 1,
        ]);
        $this->assertSame('healthy', $gatewayProvider->fresh()->last_health_status);
    }

    public function test_checkout_fails_validation_for_a_currency_the_chosen_gateway_does_not_support(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $this->gatewayProvider(); // supports UGX only
        $paymentLink = PaymentLink::factory()->create(['business_id' => $business->id]);

        Http::fake(['*/api/Auth/RequestToken' => Http::response(['token' => 'fake-token', 'status' => '200'])]);

        $response = $this->post('/pay/'.$paymentLink->id, [
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.test',
            'currency' => 'KES',
            'payment_method' => 'card',
        ]);

        $response->assertSessionHasErrors('currency');
        $this->assertDatabaseMissing('payment_transactions', ['business_id' => $business->id]);
    }

    public function test_pesapal_webhook_reconciles_status_and_credits_the_wallet_on_completion(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $this->gatewayProvider();
        $paymentLink = PaymentLink::factory()->create(['business_id' => $business->id, 'amount' => 2500]);

        $transaction = PaymentTransaction::create([
            'business_id' => $business->id,
            'payment_link_id' => $paymentLink->id,
            'provider' => 'pesapal',
            'amount' => 2500,
            'currency' => 'UGX',
            'status' => 'processing',
            'external_reference' => 'txn-ref-1',
            'provider_reference' => 'tracking-abc-123',
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.test',
        ]);

        Http::fake([
            '*/api/Auth/RequestToken' => Http::response(['token' => 'fake-token', 'status' => '200']),
            '*/api/Transactions/GetTransactionStatus*' => Http::response([
                'payment_status_code' => 1,
                'payment_status_description' => 'COMPLETED',
                'confirmation_code' => 'CONF123',
                'status' => '200',
            ]),
        ]);

        // Pesapal's IPN deliberately carries no status — reconciliation must
        // still land on "completed" purely from the status-check response.
        $response = $this->post('/webhooks/pesapal', [
            'OrderTrackingId' => 'tracking-abc-123',
            'OrderMerchantReference' => 'txn-ref-1',
            'OrderNotificationType' => 'IPNCHANGE',
        ]);

        $response->assertOk();
        $response->assertJson(['status' => 200]);

        $this->assertSame('completed', $transaction->fresh()->status->value);

        $wallet = $business->wallet->fresh();
        $this->assertSame('2500.00', (string) $wallet->available_balance);
        $this->assertSame('2500.00', (string) $wallet->settlement_balance);
    }

    public function test_pesapal_webhook_does_not_credit_the_wallet_when_status_check_says_failed(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $this->gatewayProvider();
        $paymentLink = PaymentLink::factory()->create(['business_id' => $business->id, 'amount' => 2500]);

        $transaction = PaymentTransaction::create([
            'business_id' => $business->id,
            'payment_link_id' => $paymentLink->id,
            'provider' => 'pesapal',
            'amount' => 2500,
            'currency' => 'UGX',
            'status' => 'processing',
            'external_reference' => 'txn-ref-2',
            'provider_reference' => 'tracking-xyz-999',
        ]);

        Http::fake([
            '*/api/Auth/RequestToken' => Http::response(['token' => 'fake-token', 'status' => '200']),
            '*/api/Transactions/GetTransactionStatus*' => Http::response([
                'payment_status_code' => 2,
                'payment_status_description' => 'FAILED',
                'status' => '200',
            ]),
        ]);

        $this->post('/webhooks/pesapal', [
            'OrderTrackingId' => 'tracking-xyz-999',
            'OrderMerchantReference' => 'txn-ref-2',
        ])->assertOk();

        $this->assertSame('failed', $transaction->fresh()->status->value);
        $this->assertSame('0.00', (string) $business->wallet->fresh()->available_balance);
    }
}
