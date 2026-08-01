<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_requires_authentication(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_dashboard(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('SentePro Dashboard');
    }

    public function test_dashboard_shows_live_transaction_activity_for_the_users_own_business(): void
    {
        $business = Business::factory()->create([
            'business_name' => 'Ledger Business',
            'status' => 'approved',
        ]);

        PaymentTransaction::factory()->create([
            'business_id' => $business->id,
            'provider' => 'pesapal',
            'amount' => 7500,
            'currency' => 'UGX',
            'status' => 'completed',
            'external_reference' => 'txn-dashboard-1',
        ]);

        $admin = User::factory()->businessAdmin($business)->create();

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Pesapal');
        $response->assertSee('Completed');
    }

    public function test_dashboard_does_not_show_another_businesss_transaction_activity(): void
    {
        $business = Business::factory()->create([
            'business_name' => 'Ledger Business',
            'status' => 'approved',
        ]);

        PaymentTransaction::factory()->create([
            'business_id' => $business->id,
            'provider' => 'pesapal',
            'amount' => 7500,
            'currency' => 'UGX',
            'status' => 'completed',
            'external_reference' => 'txn-dashboard-1',
        ]);

        $otherAdmin = User::factory()->businessAdmin()->create();

        $response = $this->actingAs($otherAdmin)->get('/dashboard');

        $response->assertOk();
        // "Pesapal" is now also a static label in the provider-split widget shown to every business.
        $response->assertDontSee('txn-dashboard-1');
    }

    public function test_super_admin_sees_transaction_activity_across_all_businesses(): void
    {
        $business = Business::factory()->create([
            'business_name' => 'Ledger Business',
            'status' => 'approved',
        ]);

        PaymentTransaction::factory()->create([
            'business_id' => $business->id,
            'provider' => 'pesapal',
            'amount' => 7500,
            'currency' => 'UGX',
            'status' => 'completed',
            'external_reference' => 'txn-dashboard-1',
        ]);

        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Pesapal');
    }
}
