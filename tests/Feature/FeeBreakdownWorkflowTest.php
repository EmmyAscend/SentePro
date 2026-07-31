<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeeBreakdownWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_admin_can_create_a_fee_breakdown_for_a_transaction(): void
    {
        $business = Business::factory()->create([
            'business_name' => 'Fee Business',
            'status' => 'approved',
        ]);

        $user = User::factory()->create([
            'role' => 'business_admin',
            'business_id' => $business->id,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->post('/fee-breakdowns', [
            'business_id' => $business->id,
            'transaction_reference' => 'txn-feedemo',
            'gateway_fee' => 200,
            'platform_fee' => 100,
            'net_amount' => 2200,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('fee_breakdowns', [
            'business_id' => $business->id,
            'transaction_reference' => 'txn-feedemo',
            'gateway_fee' => 200,
            'platform_fee' => 100,
        ]);
    }
}
