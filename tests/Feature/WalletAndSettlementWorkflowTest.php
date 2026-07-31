<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\PaymentTransaction;
use App\Models\SettlementMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletAndSettlementWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_registration_creates_a_wallet_with_zero_balances(): void
    {
        $business = Business::create([
            'business_name' => 'Settlement Test Business',
            'trading_name' => 'Settlement Test',
            'registration_number' => 'REG-789',
            'country' => 'Uganda',
            'phone' => '+256700000111',
            'email' => 'wallet@test.com',
            'industry' => 'Payments',
            'expected_monthly_volume' => '250000',
            'business_description' => 'Wallet test business.',
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('wallets', [
            'business_id' => $business->id,
            'available_balance' => 0,
            'pending_balance' => 0,
            'reserved_balance' => 0,
            'settlement_balance' => 0,
        ]);
    }

    public function test_completed_payment_transaction_updates_business_wallet_and_settlement_readiness(): void
    {
        $business = Business::factory()->create([
            'business_name' => 'Settlement Test Business',
            'status' => 'approved',
        ]);

        PaymentTransaction::factory()->create([
            'business_id' => $business->id,
            'provider' => 'pesapal',
            'amount' => 2500,
            'currency' => 'UGX',
            'status' => 'completed',
            'external_reference' => 'txn-wallet-1',
        ]);

        $wallet = $business->wallet()->first();

        $this->assertSame('2500.00', (string) $wallet->available_balance);
        $this->assertSame('2500.00', (string) $wallet->settlement_balance);
    }

    public function test_business_admin_can_request_a_settlement_from_their_own_wallet(): void
    {
        $business = Business::factory()->create([
            'business_name' => 'Settlement Test Business',
            'status' => 'approved',
        ]);
        $business->wallet->update(['available_balance' => 5000]);
        $method = SettlementMethod::factory()->create();

        $admin = User::factory()->businessAdmin($business)->create();

        $response = $this->actingAs($admin)->post('/settlements', [
            'amount' => 2500,
            'settlement_method_id' => $method->id,
            'notes' => 'Quarterly settlement request',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('settlements', [
            'business_id' => $business->id,
            'settlement_method_id' => $method->id,
            'amount' => 2500,
            'net_amount' => 2500,
            'status' => 'pending',
        ]);

        $wallet = $business->wallet->fresh();
        $this->assertSame('2500.00', (string) $wallet->available_balance);
        $this->assertSame('2500.00', (string) $wallet->pending_balance);
    }

    public function test_settlement_request_is_rejected_when_it_exceeds_available_balance(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $business->wallet->update(['available_balance' => 1000]);
        $method = SettlementMethod::factory()->create();

        $admin = User::factory()->businessAdmin($business)->create();

        $response = $this->actingAs($admin)->post('/settlements', [
            'amount' => 2500,
            'settlement_method_id' => $method->id,
        ]);

        $response->assertSessionHasErrors('amount');
        $this->assertDatabaseMissing('settlements', [
            'business_id' => $business->id,
        ]);
    }

    public function test_staff_without_permission_cannot_request_a_settlement(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $business->wallet->update(['available_balance' => 5000]);
        $method = SettlementMethod::factory()->create();

        $staff = User::factory()->staff($business)->create();

        $response = $this->actingAs($staff)->post('/settlements', [
            'amount' => 2500,
            'settlement_method_id' => $method->id,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('settlements', [
            'business_id' => $business->id,
        ]);
    }

    public function test_staff_with_permission_can_request_a_settlement(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $business->wallet->update(['available_balance' => 5000]);
        $method = SettlementMethod::factory()->create();

        $staff = User::factory()->staff($business, ['settlements.create'])->create();

        $response = $this->actingAs($staff)->post('/settlements', [
            'amount' => 2500,
            'settlement_method_id' => $method->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('settlements', [
            'business_id' => $business->id,
            'amount' => 2500,
        ]);
    }

    public function test_a_client_supplied_business_id_cannot_redirect_a_settlement_to_another_business(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $business->wallet->update(['available_balance' => 5000]);
        $otherBusiness = Business::factory()->create(['status' => 'approved']);
        $method = SettlementMethod::factory()->create();

        $admin = User::factory()->businessAdmin($business)->create();

        $response = $this->actingAs($admin)->post('/settlements', [
            'business_id' => $otherBusiness->id,
            'amount' => 2500,
            'settlement_method_id' => $method->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('settlements', [
            'business_id' => $business->id,
            'amount' => 2500,
        ]);
        $this->assertDatabaseMissing('settlements', [
            'business_id' => $otherBusiness->id,
        ]);
    }
}
