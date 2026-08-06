<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\GatewayProvider;
use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EgoSmsNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_completed_transaction_with_a_customer_phone_sends_an_sms(): void
    {
        Http::fake([
            '*/api/v1/json/' => Http::response(['Status' => 'OK', 'Cost' => '35', 'MsgFollowUpUniqueCode' => 'abc123']),
        ]);

        $business = Business::factory()->create(['status' => 'approved']);

        PaymentTransaction::factory()->create([
            'business_id' => $business->id,
            'status' => 'completed',
            'external_reference' => 'txn-sms-1',
            'customer_phone' => '+256700111222',
        ]);

        // PhoneNumber::normalizeUganda() strips the leading "+" before this
        // reaches EgoSMS — same normalization applied at every external
        // phone-number boundary, not just Yo Payments' Account field.
        Http::assertSent(function ($request) {
            return $request->url() === 'https://www.egosms.co/api/v1/json/'
                && $request['msgdata'][0]['number'] === '256700111222'
                && $request['method'] === 'SendSms';
        });
    }

    public function test_a_completed_transaction_with_no_customer_phone_sends_no_sms(): void
    {
        Http::fake([
            '*/api/v1/json/' => Http::response(['Status' => 'OK']),
        ]);

        $business = Business::factory()->create(['status' => 'approved']);

        PaymentTransaction::factory()->create([
            'business_id' => $business->id,
            'status' => 'completed',
            'external_reference' => 'txn-sms-2',
        ]);

        Http::assertNothingSent();
    }

    public function test_a_successful_refund_with_a_customer_phone_sends_an_sms(): void
    {
        Http::fake([
            '*/api/Auth/RequestToken' => Http::response(['token' => 'fake-token', 'status' => '200']),
            '*/api/Transactions/GetTransactionStatus*' => Http::response([
                'payment_status_code' => 1,
                'confirmation_code' => 'CONF-SMS-1',
                'status' => '200',
            ]),
            '*/api/Transactions/RefundRequest' => Http::response(['status' => '200', 'message' => 'Refund accepted']),
            '*/api/v1/json/' => Http::response(['Status' => 'OK']),
        ]);

        $business = Business::factory()->create(['status' => 'approved']);
        GatewayProvider::create([
            'provider' => 'pesapal',
            'status' => 'active',
            'environment' => 'sandbox',
            'webhook_url' => 'https://example.test/webhooks/pesapal',
            'credentials' => ['consumer_key' => 'test-key', 'consumer_secret' => 'test-secret'],
            'supported_currencies' => 'UGX',
        ]);
        $transaction = PaymentTransaction::factory()->create([
            'business_id' => $business->id,
            'provider' => 'pesapal',
            'amount' => 2500,
            'currency' => 'UGX',
            'status' => 'completed',
            'external_reference' => 'txn-sms-refund-1',
            'provider_reference' => 'tracking-sms-refund-1',
            'customer_phone' => '+256700333444',
        ]);
        $admin = User::factory()->businessAdmin($business)->create();

        $this->actingAs($admin)->post("/transactions/{$transaction->id}/refund")->assertRedirect();

        Http::assertSent(function ($request) {
            return $request->url() === 'https://www.egosms.co/api/v1/json/'
                && $request['msgdata'][0]['number'] === '256700333444';
        });
    }

    public function test_an_egosms_api_level_failure_does_not_disrupt_the_payment_credit(): void
    {
        Http::fake([
            '*/api/v1/json/' => Http::response(['Status' => 'Failed', 'Message' => 'Insufficient account balance']),
        ]);

        $business = Business::factory()->create(['status' => 'approved']);

        PaymentTransaction::factory()->create([
            'business_id' => $business->id,
            'amount' => 4000,
            'status' => 'completed',
            'external_reference' => 'txn-sms-3',
            'customer_phone' => '+256700555666',
        ]);

        $wallet = $business->wallet->fresh();
        $this->assertSame('4000.00', (string) $wallet->available_balance);
    }

    public function test_an_egosms_transport_failure_does_not_disrupt_a_refund(): void
    {
        Http::fake([
            '*/api/Auth/RequestToken' => Http::response(['token' => 'fake-token', 'status' => '200']),
            '*/api/Transactions/GetTransactionStatus*' => Http::response([
                'payment_status_code' => 1,
                'confirmation_code' => 'CONF-SMS-2',
                'status' => '200',
            ]),
            '*/api/Transactions/RefundRequest' => Http::response(['status' => '200', 'message' => 'Refund accepted']),
            '*/api/v1/json/' => function () {
                throw new ConnectionException('Could not connect to EgoSMS');
            },
        ]);

        $business = Business::factory()->create(['status' => 'approved']);
        GatewayProvider::create([
            'provider' => 'pesapal',
            'status' => 'active',
            'environment' => 'sandbox',
            'webhook_url' => 'https://example.test/webhooks/pesapal',
            'credentials' => ['consumer_key' => 'test-key', 'consumer_secret' => 'test-secret'],
            'supported_currencies' => 'UGX',
        ]);
        $transaction = PaymentTransaction::factory()->create([
            'business_id' => $business->id,
            'provider' => 'pesapal',
            'amount' => 2500,
            'currency' => 'UGX',
            'status' => 'completed',
            'external_reference' => 'txn-sms-refund-2',
            'provider_reference' => 'tracking-sms-refund-2',
            'customer_phone' => '+256700777888',
        ]);
        $admin = User::factory()->businessAdmin($business)->create();

        $response = $this->actingAs($admin)->post("/transactions/{$transaction->id}/refund");

        $response->assertRedirect(route('transactions.index'));
        $this->assertSame('refunded', $transaction->fresh()->status->value);
        $this->assertSame('0.00', (string) $business->wallet->fresh()->available_balance);
    }
}
