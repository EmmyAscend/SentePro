<?php

namespace Tests\Feature;

use App\Enums\PaymentProvider;
use App\Models\Business;
use App\Models\FeeStructure;
use App\Models\GatewayProvider;
use App\Models\PaymentLink;
use App\Models\PaymentTransaction;
use App\Models\User;
use App\Services\TransactionFeeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TransactionFeeEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculate_applies_percentage_and_flat_fees(): void
    {
        $fee = FeeStructure::create([
            'provider' => 'pesapal',
            'gateway_fee_percent' => 3,
            'gateway_fee_flat' => 50,
            'platform_fee_percent' => 1,
            'platform_fee_flat' => 20,
        ]);

        $result = app(TransactionFeeService::class)->calculate($fee, 10000);

        $this->assertSame(350.0, $result['gatewayFee']); // 10000*3% + 50
        $this->assertSame(120.0, $result['platformFee']); // 10000*1% + 20
        $this->assertSame(470.0, $result['totalFee']);
        $this->assertSame(9530.0, $result['netAmount']);
    }

    public function test_calculate_raises_the_total_fee_up_to_the_configured_minimum(): void
    {
        $fee = FeeStructure::create([
            'provider' => 'pesapal',
            'gateway_fee_percent' => 1,
            'platform_fee_percent' => 0,
            'min_fee' => 500,
        ]);

        // 1% of 1000 is only 10, below the 500 minimum
        $result = app(TransactionFeeService::class)->calculate($fee, 1000);

        $this->assertSame(500.0, $result['totalFee']);
        $this->assertSame(500.0, $result['netAmount']);
    }

    public function test_calculate_caps_the_total_fee_at_the_configured_maximum_without_reducing_gateway_fee(): void
    {
        $fee = FeeStructure::create([
            'provider' => 'pesapal',
            'gateway_fee_percent' => 3,
            'platform_fee_percent' => 5,
            'max_fee' => 400,
        ]);

        // amount=10000: gateway=300, platform=500, total=800 pre-clamp
        $result = app(TransactionFeeService::class)->calculate($fee, 10000);

        $this->assertSame(300.0, $result['gatewayFee']); // untouched by the cap
        $this->assertSame(100.0, $result['platformFee']); // absorbed the reduction
        $this->assertSame(400.0, $result['totalFee']);
        $this->assertSame(9600.0, $result['netAmount']);
    }

    public function test_resolve_prefers_a_business_specific_fee_structure_over_the_platform_default(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);

        FeeStructure::create(['provider' => 'pesapal', 'business_id' => null, 'platform_fee_percent' => 5]);
        $override = FeeStructure::create(['provider' => 'pesapal', 'business_id' => $business->id, 'platform_fee_percent' => 1]);

        $resolved = app(TransactionFeeService::class)->resolve(PaymentProvider::Pesapal, $business);

        $this->assertSame($override->id, $resolved->id);
    }

    public function test_resolve_falls_back_to_the_platform_default_when_no_business_override_exists(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $default = FeeStructure::create(['provider' => 'pesapal', 'business_id' => null, 'platform_fee_percent' => 2]);

        $resolved = app(TransactionFeeService::class)->resolve(PaymentProvider::Pesapal, $business);

        $this->assertSame($default->id, $resolved->id);
    }

    public function test_resolve_returns_null_when_nothing_matches(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);

        $this->assertNull(app(TransactionFeeService::class)->resolve(PaymentProvider::Pesapal, $business));
    }

    public function test_a_completed_transaction_with_no_fee_structure_credits_the_full_gross_amount(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);

        PaymentTransaction::factory()->create([
            'business_id' => $business->id,
            'provider' => 'pesapal',
            'amount' => 2500,
            'status' => 'completed',
        ]);

        $this->assertSame('2500.00', (string) $business->wallet->fresh()->available_balance);
        $this->assertDatabaseHas('fee_breakdowns', [
            'business_id' => $business->id,
            'gateway_fee' => 0,
            'platform_fee' => 0,
            'net_amount' => 2500,
        ]);
    }

    public function test_pesapal_webhook_completion_auto_records_a_fee_breakdown_and_credits_only_the_net_amount(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        FeeStructure::create([
            'provider' => 'pesapal',
            'business_id' => null,
            'gateway_fee_percent' => 3,
            'platform_fee_percent' => 1,
        ]);

        GatewayProvider::create([
            'provider' => 'pesapal',
            'status' => 'active',
            'environment' => 'sandbox',
            'webhook_url' => 'https://example.test/webhooks/pesapal',
            'credentials' => ['consumer_key' => 'test', 'consumer_secret' => 'test'],
            'supported_currencies' => 'UGX',
        ]);

        $paymentLink = PaymentLink::factory()->create(['business_id' => $business->id, 'amount' => 10000]);

        $transaction = PaymentTransaction::create([
            'business_id' => $business->id,
            'payment_link_id' => $paymentLink->id,
            'provider' => 'pesapal',
            'amount' => 10000,
            'currency' => 'UGX',
            'status' => 'processing',
            'external_reference' => 'txn-fee-1',
            'provider_reference' => 'tracking-fee-1',
        ]);

        Http::fake([
            '*/api/Auth/RequestToken' => Http::response(['token' => 'fake-token', 'status' => '200']),
            '*/api/Transactions/GetTransactionStatus*' => Http::response([
                'payment_status_code' => 1,
                'confirmation_code' => 'CONF1',
                'status' => '200',
            ]),
        ]);

        $this->post('/webhooks/pesapal', [
            'OrderTrackingId' => 'tracking-fee-1',
            'OrderMerchantReference' => 'txn-fee-1',
        ])->assertOk();

        $this->assertSame('completed', $transaction->fresh()->status->value);

        $this->assertDatabaseHas('fee_breakdowns', [
            'business_id' => $business->id,
            'transaction_reference' => 'txn-fee-1',
            'gateway_fee' => 300,
            'platform_fee' => 100,
            'net_amount' => 9600,
        ]);

        $wallet = $business->wallet->fresh();
        $this->assertSame('9600.00', (string) $wallet->available_balance);
        $this->assertSame('9600.00', (string) $wallet->settlement_balance);
    }

    public function test_super_admin_can_create_a_fee_structure(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->post('/admin/fee-structures', [
            'provider' => 'yo_payments',
            'status' => 'enabled',
            'gateway_fee_percent' => 2,
            'gateway_fee_flat' => 0,
            'platform_fee_percent' => 1,
            'platform_fee_flat' => 0,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('fee_structures', [
            'provider' => 'yo_payments',
            'business_id' => null,
        ]);
    }

    public function test_business_admin_cannot_manage_fee_structures(): void
    {
        $businessAdmin = User::factory()->businessAdmin()->create();

        $response = $this->actingAs($businessAdmin)->get('/admin/fee-structures');

        $response->assertForbidden();
    }
}
