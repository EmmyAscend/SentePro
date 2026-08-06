<?php

namespace Tests\Feature\Api;

use App\Models\Business;
use App\Models\PaymentLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PaymentLinkApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_token_can_create_a_payment_link(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $admin = User::factory()->businessAdmin($business)->create();

        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/v1/payment-links', [
            'title' => 'API-created link',
            'type' => 'donation',
            'amount' => 5000,
            'currency' => 'UGX',
            'custom_amount' => false,
            'expiry_date' => now()->addDays(30)->toDateString(),
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.title', 'API-created link');
        $response->assertJsonStructure(['data' => ['id', 'checkout_url']]);

        $this->assertDatabaseHas('payment_links', [
            'business_id' => $business->id,
            'title' => 'API-created link',
        ]);
    }

    public function test_a_token_can_list_and_show_its_own_business_payment_links(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $admin = User::factory()->businessAdmin($business)->create();
        $link = PaymentLink::factory()->create(['business_id' => $business->id, 'title' => 'My Link']);

        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/payment-links')->assertOk()->assertJsonFragment(['title' => 'My Link']);
        $this->getJson("/api/v1/payment-links/{$link->id}")->assertOk()->assertJsonPath('data.title', 'My Link');
    }

    public function test_a_token_cannot_see_another_businesss_payment_link(): void
    {
        $businessA = Business::factory()->create(['status' => 'approved']);
        $businessB = Business::factory()->create(['status' => 'approved']);
        $adminA = User::factory()->businessAdmin($businessA)->create();
        $linkB = PaymentLink::factory()->create(['business_id' => $businessB->id, 'title' => 'Not yours']);

        Sanctum::actingAs($adminA);

        $this->getJson('/api/v1/payment-links')->assertOk()->assertJsonMissing(['title' => 'Not yours']);
        $this->getJson("/api/v1/payment-links/{$linkB->id}")->assertNotFound();
    }
}
