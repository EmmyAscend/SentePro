<?php

namespace Tests\Feature\Api;

use App\Models\Business;
use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PaymentTransactionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_token_can_list_and_check_the_status_of_its_own_transactions(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $admin = User::factory()->businessAdmin($business)->create();
        $transaction = PaymentTransaction::factory()->create([
            'business_id' => $business->id,
            'status' => 'completed',
            'external_reference' => 'txn-api-1',
        ]);

        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/transactions')->assertOk()->assertJsonFragment(['external_reference' => 'txn-api-1']);

        $response = $this->getJson("/api/v1/transactions/{$transaction->id}");
        $response->assertOk();
        $response->assertJsonPath('data.status', 'completed');
    }

    public function test_a_token_cannot_see_another_businesss_transaction(): void
    {
        $businessA = Business::factory()->create(['status' => 'approved']);
        $businessB = Business::factory()->create(['status' => 'approved']);
        $adminA = User::factory()->businessAdmin($businessA)->create();
        $transactionB = PaymentTransaction::factory()->create([
            'business_id' => $businessB->id,
            'external_reference' => 'txn-not-yours',
        ]);

        Sanctum::actingAs($adminA);

        $this->getJson('/api/v1/transactions')->assertOk()->assertJsonMissing(['external_reference' => 'txn-not-yours']);
        $this->getJson("/api/v1/transactions/{$transactionB->id}")->assertNotFound();
    }
}
