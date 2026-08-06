<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\GatewayProvider;
use App\Models\PaymentLink;
use App\Models\PaymentTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CheckoutStatusPollingTest extends TestCase
{
    use RefreshDatabase;

    private function pesapalProvider(): GatewayProvider
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

    public function test_polling_a_still_processing_transaction_actively_rechecks_the_gateway(): void
    {
        $this->pesapalProvider();
        $business = Business::factory()->create(['status' => 'approved']);
        $paymentLink = PaymentLink::factory()->create(['business_id' => $business->id, 'amount' => 2500]);
        $transaction = PaymentTransaction::create([
            'business_id' => $business->id,
            'payment_link_id' => $paymentLink->id,
            'provider' => 'pesapal',
            'amount' => 2500,
            'currency' => 'UGX',
            'status' => 'processing',
            'external_reference' => 'txn-poll-1',
            'provider_reference' => 'tracking-poll-1',
        ]);

        Http::fake([
            '*/api/Auth/RequestToken' => Http::response(['token' => 'fake-token', 'status' => '200']),
            '*/api/Transactions/GetTransactionStatus*' => Http::response([
                'payment_status_code' => 1,
                'confirmation_code' => 'CONF-POLL-1',
                'status' => '200',
            ]),
        ]);

        $response = $this->getJson("/pay/{$paymentLink->id}/status/check");

        $response->assertOk();
        $response->assertJson(['status' => 'completed']);
        $this->assertNotNull($response->json('receipt_url'));
        $this->assertSame('completed', $transaction->fresh()->status->value);
    }

    public function test_polling_reports_processing_while_the_gateway_still_says_pending(): void
    {
        $this->pesapalProvider();
        $business = Business::factory()->create(['status' => 'approved']);
        $paymentLink = PaymentLink::factory()->create(['business_id' => $business->id, 'amount' => 2500]);
        PaymentTransaction::create([
            'business_id' => $business->id,
            'payment_link_id' => $paymentLink->id,
            'provider' => 'pesapal',
            'amount' => 2500,
            'currency' => 'UGX',
            'status' => 'processing',
            'external_reference' => 'txn-poll-2',
            'provider_reference' => 'tracking-poll-2',
        ]);

        Http::fake([
            '*/api/Auth/RequestToken' => Http::response(['token' => 'fake-token', 'status' => '200']),
            '*/api/Transactions/GetTransactionStatus*' => Http::response([
                // Any code outside Pesapal's completed(1)/failed(0,2,3) set
                // maps to "processing" in PesapalDriver::checkStatus().
                'payment_status_code' => 99,
                'status' => '200',
            ]),
        ]);

        $response = $this->getJson("/pay/{$paymentLink->id}/status/check");

        $response->assertOk();
        $response->assertJson(['status' => 'processing', 'receipt_url' => null]);
    }

    public function test_polling_a_completed_transaction_does_not_call_the_gateway_again(): void
    {
        $this->pesapalProvider();
        $business = Business::factory()->create(['status' => 'approved']);
        $paymentLink = PaymentLink::factory()->create(['business_id' => $business->id, 'amount' => 2500]);
        $transaction = PaymentTransaction::create([
            'business_id' => $business->id,
            'payment_link_id' => $paymentLink->id,
            'provider' => 'pesapal',
            'amount' => 2500,
            'currency' => 'UGX',
            'status' => 'completed',
            'external_reference' => 'txn-poll-3',
            'provider_reference' => 'tracking-poll-3',
        ]);

        Http::fake();

        $response = $this->getJson("/pay/{$paymentLink->id}/status/check");

        $response->assertOk();
        $response->assertJson(['status' => 'completed']);
        Http::assertNothingSent();
        // Creating the transaction already-completed still runs
        // applyFeeAndCredit() (PaymentTransaction::booted()'s created hook),
        // so a receipt exists — the endpoint should report its URL.
        $this->assertNotNull($transaction->fresh()->receipt);
        $this->assertSame(route('receipts.show', $transaction->fresh()->receipt), $response->json('receipt_url'));
    }

    public function test_polling_swallows_a_gateway_failure_and_returns_the_last_known_status(): void
    {
        $this->pesapalProvider();
        $business = Business::factory()->create(['status' => 'approved']);
        $paymentLink = PaymentLink::factory()->create(['business_id' => $business->id, 'amount' => 2500]);
        PaymentTransaction::create([
            'business_id' => $business->id,
            'payment_link_id' => $paymentLink->id,
            'provider' => 'pesapal',
            'amount' => 2500,
            'currency' => 'UGX',
            'status' => 'processing',
            'external_reference' => 'txn-poll-4',
            'provider_reference' => 'tracking-poll-4',
        ]);

        Http::fake([
            '*/api/Auth/RequestToken' => Http::response(['error' => 'invalid_client'], 401),
        ]);

        $response = $this->getJson("/pay/{$paymentLink->id}/status/check");

        $response->assertOk();
        $response->assertJson(['status' => 'processing']);
    }

    public function test_polling_a_payment_link_with_no_transaction_yet(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $paymentLink = PaymentLink::factory()->create(['business_id' => $business->id]);

        $response = $this->getJson("/pay/{$paymentLink->id}/status/check");

        $response->assertOk();
        $response->assertJson(['status' => 'not_found']);
    }
}
