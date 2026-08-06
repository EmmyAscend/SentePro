<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GatewayAndTransactionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_admin_can_create_a_payment_transaction_record(): void
    {
        $business = Business::factory()->create([
            'business_name' => 'Gateway Business',
            'status' => 'approved',
        ]);

        $user = User::factory()->create([
            'role' => 'business_admin',
            'business_id' => $business->id,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->post('/transactions', [
            'business_id' => $business->id,
            'provider' => 'pesapal',
            'amount' => 7500,
            'currency' => 'UGX',
            'status' => 'processing',
            'external_reference' => 'txn-1001',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('payment_transactions', [
            'business_id' => $business->id,
            'provider' => 'pesapal',
            'currency' => 'UGX',
            'status' => 'processing',
            'external_reference' => 'txn-1001',
        ]);
    }
}
