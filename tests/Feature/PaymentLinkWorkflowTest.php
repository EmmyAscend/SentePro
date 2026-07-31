<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentLinkWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_business_admin_can_create_a_payment_link(): void
    {
        $business = Business::factory()->create([
            'business_name' => 'PayLink Business',
            'status' => 'approved',
        ]);

        $user = User::factory()->create([
            'role' => 'business_admin',
            'business_id' => $business->id,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->post('/payment-links', [
            'business_id' => $business->id,
            'title' => 'Community Support Fund',
            'type' => 'donation',
            'amount' => 2500,
            'custom_amount' => true,
            'expiry_date' => now()->addDays(7)->toDateString(),
            'description' => 'Support the community initiative.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('payment_links', [
            'business_id' => $business->id,
            'title' => 'Community Support Fund',
            'type' => 'donation',
            'status' => 'active',
        ]);
    }
}
