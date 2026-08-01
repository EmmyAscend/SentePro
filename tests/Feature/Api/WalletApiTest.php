<?php

namespace Tests\Feature\Api;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WalletApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_token_can_fetch_its_own_business_wallet_balance(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $business->wallet->update(['available_balance' => 5000, 'settlement_balance' => 2500]);
        $admin = User::factory()->businessAdmin($business)->create();

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/v1/wallet');

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'available_balance' => 5000.0,
                'settlement_balance' => 2500.0,
            ],
        ]);
    }

    public function test_a_real_bearer_token_authenticates_end_to_end(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $admin = User::factory()->businessAdmin($business)->create();
        $token = $admin->createToken('Integration test');

        $response = $this->withHeaders(['Authorization' => "Bearer {$token->plainTextToken}"])
            ->getJson('/api/v1/wallet');

        $response->assertOk();
    }

    public function test_a_request_with_no_token_is_rejected(): void
    {
        $response = $this->getJson('/api/v1/wallet');

        $response->assertUnauthorized();
    }

    public function test_a_request_with_an_invalid_token_is_rejected(): void
    {
        $response = $this->withHeaders(['Authorization' => 'Bearer not-a-real-token'])
            ->getJson('/api/v1/wallet');

        $response->assertUnauthorized();
    }
}
