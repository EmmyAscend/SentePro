<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\PublicHoliday;
use App\Models\Settlement;
use App\Models\SettlementMethod;
use App\Models\User;
use App\Services\SettlementEstimateService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettlementConfigurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_a_settlement_method(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->post('/admin/settlement-methods', [
            'code' => 'airtel_money',
            'name' => 'Airtel Money',
            'status' => 'enabled',
            'processing_time' => 12,
            'time_unit' => 'hours',
            'settlement_fee_percent' => 2,
            'settlement_fee_flat' => 0,
            'platform_fee_percent' => 1,
            'platform_fee_flat' => 0,
            'auto_approval' => 0,
            'weekend_processing' => 1,
            'public_description' => 'Within 12 hours',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('settlement_methods', [
            'code' => 'airtel_money',
            'name' => 'Airtel Money',
        ]);
    }

    public function test_business_admin_cannot_manage_settlement_methods(): void
    {
        $businessAdmin = User::factory()->businessAdmin()->create();

        $response = $this->actingAs($businessAdmin)->get('/admin/settlement-methods');

        $response->assertForbidden();
    }

    public function test_estimate_computes_gateway_and_platform_fees(): void
    {
        $method = SettlementMethod::factory()->create([
            'settlement_fee_percent' => 2,
            'settlement_fee_flat' => 0,
            'platform_fee_percent' => 1,
            'platform_fee_flat' => 500,
        ]);

        $estimate = app(SettlementEstimateService::class)->estimate($method, 100000);

        $this->assertSame(2000.0, $estimate['gatewayFee']);
        $this->assertSame(1500.0, $estimate['platformFee']);
        $this->assertSame(3500.0, $estimate['totalFee']);
        $this->assertSame(96500.0, $estimate['netAmount']);
    }

    public function test_working_days_estimate_skips_weekends_when_a_friday_evening_request_arrives_after_cutoff(): void
    {
        $method = SettlementMethod::factory()->create([
            'processing_time' => 1,
            'time_unit' => 'working_days',
            'weekend_processing' => false,
        ]);

        $friday = CarbonImmutable::now()->next(CarbonImmutable::FRIDAY)->setTime(18, 0);

        $completion = app(SettlementEstimateService::class)->calculateCompletionAt($method, $friday);

        // Past the (default 17:00) cutoff on Friday, weekend not processed:
        // pushed to Saturday morning, then 1 working day lands on Monday.
        $this->assertTrue($completion->isMonday());
    }

    public function test_working_days_estimate_skips_a_configured_public_holiday(): void
    {
        $method = SettlementMethod::factory()->create([
            'processing_time' => 1,
            'time_unit' => 'working_days',
            'weekend_processing' => false,
        ]);

        $monday = CarbonImmutable::now()->next(CarbonImmutable::MONDAY)->setTime(9, 0);
        PublicHoliday::create(['date' => $monday->addDay()->toDateString(), 'label' => 'Test Holiday']);

        $completion = app(SettlementEstimateService::class)->calculateCompletionAt($method, $monday);

        // Tuesday is a holiday, so 1 working day from Monday lands on Wednesday.
        $this->assertTrue($completion->isWednesday());
    }

    public function test_settlement_request_is_rejected_below_the_methods_minimum_amount(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $business->wallet->update(['available_balance' => 10000]);
        $method = SettlementMethod::factory()->create(['min_amount' => 1000]);

        $admin = User::factory()->businessAdmin($business)->create();

        $response = $this->actingAs($admin)->post('/settlements', [
            'amount' => 500,
            'settlement_method_id' => $method->id,
        ]);

        $response->assertSessionHasErrors('amount');
        $this->assertDatabaseMissing('settlements', ['business_id' => $business->id]);
    }

    public function test_settlement_request_is_rejected_once_the_daily_limit_is_reached(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $business->wallet->update(['available_balance' => 10000]);
        $method = SettlementMethod::factory()->create(['daily_limit' => 3000]);

        $admin = User::factory()->businessAdmin($business)->create();

        $this->actingAs($admin)->post('/settlements', [
            'amount' => 2000,
            'settlement_method_id' => $method->id,
        ])->assertRedirect();

        $response = $this->actingAs($admin)->post('/settlements', [
            'amount' => 2000,
            'settlement_method_id' => $method->id,
        ]);

        $response->assertSessionHasErrors('amount');
        $this->assertSame(1, Settlement::where('business_id', $business->id)->count());
    }

    public function test_super_admin_completing_a_settlement_credits_the_net_amount_to_settlement_balance(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $business->wallet->update(['available_balance' => 10000]);
        $method = SettlementMethod::factory()->create([
            'settlement_fee_percent' => 2,
            'platform_fee_percent' => 1,
        ]);

        $admin = User::factory()->businessAdmin($business)->create();
        $this->actingAs($admin)->post('/settlements', [
            'amount' => 2500,
            'settlement_method_id' => $method->id,
        ])->assertRedirect();

        $settlement = Settlement::where('business_id', $business->id)->firstOrFail();
        $this->assertSame('50.00', (string) $settlement->gateway_fee);
        $this->assertSame('25.00', (string) $settlement->platform_fee);
        $this->assertSame('2425.00', (string) $settlement->net_amount);

        $superAdmin = User::factory()->superAdmin()->create();
        $response = $this->actingAs($superAdmin)->post("/admin/settlements/{$settlement->id}/complete");

        $response->assertRedirect();
        $this->assertSame('completed', $settlement->fresh()->status->value);

        $wallet = $business->wallet->fresh();
        $this->assertSame('7500.00', (string) $wallet->available_balance);
        $this->assertSame('0.00', (string) $wallet->pending_balance);
        $this->assertSame('2425.00', (string) $wallet->settlement_balance);
    }

    public function test_super_admin_rejecting_a_settlement_returns_the_full_amount_to_available_balance(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $business->wallet->update(['available_balance' => 10000]);
        $method = SettlementMethod::factory()->create();

        $admin = User::factory()->businessAdmin($business)->create();
        $this->actingAs($admin)->post('/settlements', [
            'amount' => 2500,
            'settlement_method_id' => $method->id,
        ])->assertRedirect();

        $settlement = Settlement::where('business_id', $business->id)->firstOrFail();

        $superAdmin = User::factory()->superAdmin()->create();
        $response = $this->actingAs($superAdmin)->post("/admin/settlements/{$settlement->id}/reject", [
            'reason' => 'Suspicious activity',
        ]);

        $response->assertRedirect();
        $this->assertSame('rejected', $settlement->fresh()->status->value);

        $wallet = $business->wallet->fresh();
        $this->assertSame('10000.00', (string) $wallet->available_balance);
        $this->assertSame('0.00', (string) $wallet->pending_balance);
    }

    public function test_business_admin_cannot_complete_or_reject_a_settlement(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $business->wallet->update(['available_balance' => 10000]);
        $method = SettlementMethod::factory()->create();

        $admin = User::factory()->businessAdmin($business)->create();
        $this->actingAs($admin)->post('/settlements', [
            'amount' => 2500,
            'settlement_method_id' => $method->id,
        ])->assertRedirect();

        $settlement = Settlement::where('business_id', $business->id)->firstOrFail();

        $response = $this->actingAs($admin)->post("/admin/settlements/{$settlement->id}/complete");

        $response->assertForbidden();
        $this->assertSame('pending', $settlement->fresh()->status->value);
    }
}
