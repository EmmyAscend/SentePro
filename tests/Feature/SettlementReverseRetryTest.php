<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Settlement;
use App\Models\SettlementMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettlementReverseRetryTest extends TestCase
{
    use RefreshDatabase;

    private function completedSettlement(Business $business, SettlementMethod $method): Settlement
    {
        $business->wallet->update(['settlement_balance' => 2400]);

        return Settlement::create([
            'business_id' => $business->id,
            'settlement_method_id' => $method->id,
            'amount' => 2500,
            'gateway_fee' => 75,
            'platform_fee' => 25,
            'net_amount' => 2400,
            'status' => 'completed',
        ]);
    }

    private function rejectedSettlement(Business $business, SettlementMethod $method, float $amount = 2500): Settlement
    {
        return Settlement::create([
            'business_id' => $business->id,
            'settlement_method_id' => $method->id,
            'amount' => $amount,
            'gateway_fee' => 0,
            'platform_fee' => 0,
            'net_amount' => $amount,
            'status' => 'rejected',
        ]);
    }

    public function test_super_admin_can_reverse_a_completed_settlement(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $method = SettlementMethod::factory()->create();
        $settlement = $this->completedSettlement($business, $method);
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->post("/admin/settlements/{$settlement->id}/reverse", [
            'reason' => 'Duplicate payout',
        ]);

        $response->assertRedirect(route('settlements.index'));
        $this->assertSame('reversed', $settlement->fresh()->status->value);

        $wallet = $business->wallet->fresh();
        $this->assertSame('0.00', (string) $wallet->settlement_balance);
        $this->assertSame('2500.00', (string) $wallet->available_balance);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'settlement.reversed',
            'subject_id' => $settlement->id,
        ]);
    }

    public function test_a_non_completed_settlement_cannot_be_reversed(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $method = SettlementMethod::factory()->create();
        $settlement = $this->rejectedSettlement($business, $method);
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->post("/admin/settlements/{$settlement->id}/reverse");

        $response->assertSessionHasErrors('status');
        $this->assertSame('rejected', $settlement->fresh()->status->value);
    }

    public function test_super_admin_can_retry_a_rejected_settlement(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $business->wallet->update(['available_balance' => 5000]);
        $method = SettlementMethod::factory()->create();
        $settlement = $this->rejectedSettlement($business, $method);
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->post("/admin/settlements/{$settlement->id}/retry");

        $response->assertRedirect(route('settlements.index'));
        $this->assertSame('pending', $settlement->fresh()->status->value);

        $wallet = $business->wallet->fresh();
        $this->assertSame('2500.00', (string) $wallet->available_balance);
        $this->assertSame('2500.00', (string) $wallet->pending_balance);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'settlement.retried',
            'subject_id' => $settlement->id,
        ]);
    }

    public function test_a_non_rejected_or_failed_settlement_cannot_be_retried(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $method = SettlementMethod::factory()->create();
        $settlement = $this->completedSettlement($business, $method);
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->post("/admin/settlements/{$settlement->id}/retry");

        $response->assertSessionHasErrors('status');
        $this->assertSame('completed', $settlement->fresh()->status->value);
    }

    public function test_retry_fails_when_the_settlement_method_is_now_disabled(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $business->wallet->update(['available_balance' => 5000]);
        $method = SettlementMethod::factory()->create(['status' => 'disabled']);
        $settlement = $this->rejectedSettlement($business, $method);
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->post("/admin/settlements/{$settlement->id}/retry");

        $response->assertSessionHasErrors('settlement_method_id');
        $this->assertSame('rejected', $settlement->fresh()->status->value);
    }

    public function test_retry_fails_when_the_amount_now_exceeds_available_balance(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $business->wallet->update(['available_balance' => 1000]);
        $method = SettlementMethod::factory()->create();
        $settlement = $this->rejectedSettlement($business, $method, 2500);
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->post("/admin/settlements/{$settlement->id}/retry");

        $response->assertSessionHasErrors('amount');
        $this->assertSame('rejected', $settlement->fresh()->status->value);
        $this->assertSame('1000.00', (string) $business->wallet->fresh()->available_balance);
    }

    public function test_business_admin_cannot_reverse_or_retry_a_settlement(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $method = SettlementMethod::factory()->create();
        $completed = $this->completedSettlement($business, $method);
        $rejected = $this->rejectedSettlement($business, $method);
        $admin = User::factory()->businessAdmin($business)->create();

        $this->actingAs($admin)->post("/admin/settlements/{$completed->id}/reverse")->assertForbidden();
        $this->actingAs($admin)->post("/admin/settlements/{$rejected->id}/retry")->assertForbidden();
    }
}
