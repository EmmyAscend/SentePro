<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\GatewayProvider;
use App\Models\PaymentLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    private function gatewayProviderFor(Business $business, string $name): GatewayProvider
    {
        return GatewayProvider::create([
            'business_id' => $business->id,
            'name' => $name,
            'provider' => 'pesapal',
            'status' => 'active',
            'environment' => 'sandbox',
            'webhook_url' => 'https://example.test/webhook',
            'credentials' => ['consumer_key' => 'test', 'consumer_secret' => 'test'],
            'supported_countries' => 'UG',
            'supported_currencies' => 'UGX',
        ]);
    }

    public function test_business_admin_only_sees_their_own_payment_links_in_the_index(): void
    {
        $businessA = Business::factory()->create(['status' => 'approved']);
        $businessB = Business::factory()->create(['status' => 'approved']);

        PaymentLink::factory()->create(['business_id' => $businessA->id, 'title' => 'Business A Link']);
        PaymentLink::factory()->create(['business_id' => $businessB->id, 'title' => 'Business B Link']);

        $adminA = User::factory()->businessAdmin($businessA)->create();

        $response = $this->actingAs($adminA)->get('/payment-links');

        $response->assertOk();
        $response->assertSee('Business A Link');
        $response->assertDontSee('Business B Link');
    }

    public function test_business_admin_only_sees_their_own_settlements_in_the_index(): void
    {
        $businessA = Business::factory()->create(['status' => 'approved']);
        $businessB = Business::factory()->create(['status' => 'approved']);

        $adminA = User::factory()->businessAdmin($businessA)->create();

        $response = $this->actingAs($adminA)->get('/settlements');

        $response->assertOk();
        $response->assertSee($businessA->business_name);
        $response->assertDontSee($businessB->business_name);
    }

    public function test_business_admin_only_sees_their_own_gateway_providers_in_the_index(): void
    {
        $businessA = Business::factory()->create(['status' => 'approved']);
        $businessB = Business::factory()->create(['status' => 'approved']);

        $this->gatewayProviderFor($businessA, 'Business A Gateway');
        $this->gatewayProviderFor($businessB, 'Business B Gateway');

        $adminA = User::factory()->businessAdmin($businessA)->create();

        $response = $this->actingAs($adminA)->get('/gateways');

        $response->assertOk();
        $response->assertSee('Business A Gateway');
        $response->assertDontSee('Business B Gateway');
    }

    public function test_super_admin_sees_every_businesss_payment_links(): void
    {
        $businessA = Business::factory()->create(['status' => 'approved']);
        $businessB = Business::factory()->create(['status' => 'approved']);

        PaymentLink::factory()->create(['business_id' => $businessA->id, 'title' => 'Business A Link']);
        PaymentLink::factory()->create(['business_id' => $businessB->id, 'title' => 'Business B Link']);

        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->get('/payment-links');

        $response->assertOk();
        $response->assertSee('Business A Link');
        $response->assertSee('Business B Link');
    }

    public function test_a_business_admin_cannot_create_a_gateway_provider_for_another_business_via_a_forged_business_id(): void
    {
        $businessA = Business::factory()->create(['status' => 'approved']);
        $businessB = Business::factory()->create(['status' => 'approved']);

        $adminA = User::factory()->businessAdmin($businessA)->create();

        $response = $this->actingAs($adminA)->post('/gateways', [
            'business_id' => $businessB->id,
            'name' => 'Forged Gateway',
            'provider' => 'pesapal',
            'status' => 'active',
            'environment' => 'sandbox',
            'credentials' => json_encode(['consumer_key' => 'test', 'consumer_secret' => 'test']),
            'supported_countries' => 'UG',
            'supported_currencies' => 'UGX',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('gateway_providers', [
            'name' => 'Forged Gateway',
            'business_id' => $businessA->id,
        ]);
        $this->assertDatabaseMissing('gateway_providers', [
            'name' => 'Forged Gateway',
            'business_id' => $businessB->id,
        ]);
    }
}
