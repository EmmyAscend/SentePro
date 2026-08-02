<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessStatusWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_registration_is_saved_with_pending_status(): void
    {
        $this->post('/business/register', [
            'owner_name' => 'Jane Owner',
            'owner_email' => 'owner@sentepro.test',
            'owner_password' => 'password123',
            'owner_password_confirmation' => 'password123',
            'business_type' => 'business',
            'business_name' => 'SentePro Demo Business',
            'trading_name' => 'SentePro Demo',
            'registration_number' => 'REG-123456',
            'country' => 'Uganda',
            'phone' => '+256700000000',
            'email' => 'admin@sentepro.test',
            'industry' => 'Payments',
            'expected_monthly_volume' => '1000000',
            'business_description' => 'A test business collecting payments online.',
        ]);

        $this->assertDatabaseHas('businesses', [
            'business_name' => 'SentePro Demo Business',
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'owner@sentepro.test',
            'role' => 'business_admin',
        ]);
    }

    public function test_authenticated_business_admin_can_see_their_own_pending_business_status_summary(): void
    {
        $business = Business::factory()->create([
            'business_name' => 'Demo Business',
            'status' => 'pending',
        ]);

        $admin = User::factory()->businessAdmin($business)->create();

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Pending review');
    }
}
