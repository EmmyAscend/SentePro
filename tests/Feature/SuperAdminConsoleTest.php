<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\SettlementMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminConsoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_admin_cannot_access_any_super_admin_console_page(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $admin = User::factory()->businessAdmin($business)->create();

        $this->actingAs($admin)->get('/admin/audit-logs')->assertForbidden();
        $this->actingAs($admin)->get('/admin/wallet-monitoring')->assertForbidden();
        $this->actingAs($admin)->get('/admin/businesses')->assertForbidden();
    }

    public function test_super_admin_sees_audit_log_entries_generated_by_a_settlement_request(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $business->wallet->update(['available_balance' => 5000]);
        $method = SettlementMethod::factory()->create();
        $businessAdmin = User::factory()->businessAdmin($business)->create();

        $this->actingAs($businessAdmin)->post('/settlements', [
            'amount' => 2500,
            'settlement_method_id' => $method->id,
        ])->assertRedirect();

        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->get('/admin/audit-logs');

        $response->assertOk();
        $response->assertSee('settlement.requested');
        $response->assertSee($businessAdmin->name);
    }

    public function test_super_admin_can_filter_audit_logs_by_action(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)->post("/admin/businesses/{$business->id}/review", [
            'status' => 'approved',
        ])->assertRedirect();

        $response = $this->actingAs($superAdmin)->get('/admin/audit-logs?action=business.reviewed');

        $response->assertOk();
        $response->assertSee('business.reviewed');
    }

    public function test_wallet_monitoring_totals_match_a_hand_computed_sum_across_businesses(): void
    {
        $businessA = Business::factory()->create(['status' => 'approved']);
        $businessA->wallet->update(['available_balance' => 1000, 'pending_balance' => 200, 'settlement_balance' => 300]);

        $businessB = Business::factory()->create(['status' => 'approved']);
        $businessB->wallet->update(['available_balance' => 500, 'pending_balance' => 0, 'settlement_balance' => 100]);

        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->get('/admin/wallet-monitoring');

        $response->assertOk();
        // Platform float = (1000+200+300) + (500+0+100) = 2,100.00
        $response->assertSee('2,100.00');
        $response->assertSee($businessA->business_name);
        $response->assertSee($businessB->business_name);
    }

    public function test_wallet_monitoring_is_forbidden_for_staff(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $staff = User::factory()->staff($business)->create();

        $this->actingAs($staff)->get('/admin/wallet-monitoring')->assertForbidden();
    }

    public function test_businesses_page_lists_every_business_across_tenants(): void
    {
        $businessA = Business::factory()->create(['status' => 'pending']);
        $businessB = Business::factory()->create(['status' => 'approved']);
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->get('/admin/businesses');

        $response->assertOk();
        $response->assertSee($businessA->business_name);
        $response->assertSee($businessB->business_name);
    }

    public function test_businesses_page_filters_by_status(): void
    {
        $pending = Business::factory()->create(['status' => 'pending', 'business_name' => 'Pending Biz']);
        $approved = Business::factory()->create(['status' => 'approved', 'business_name' => 'Approved Biz']);
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->get('/admin/businesses?status=approved');

        $response->assertOk();
        $response->assertSee('Approved Biz');
        $response->assertDontSee('Pending Biz');
    }

    public function test_super_admin_can_suspend_an_approved_business_from_the_businesses_page(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)->post("/admin/businesses/{$business->id}/review", [
            'status' => 'suspended',
        ])->assertRedirect();

        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'status' => 'suspended',
        ]);
    }

    public function test_super_admin_can_reinstate_a_suspended_business(): void
    {
        $business = Business::factory()->create(['status' => 'suspended']);
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)->post("/admin/businesses/{$business->id}/review", [
            'status' => 'approved',
        ])->assertRedirect();

        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'status' => 'approved',
        ]);
    }
}
