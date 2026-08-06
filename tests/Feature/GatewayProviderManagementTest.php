<?php

namespace Tests\Feature;

use App\Enums\PaymentProvider;
use App\Models\Business;
use App\Models\GatewayProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GatewayProviderManagementTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(): array
    {
        return [
            'status' => 'active',
            'environment' => 'production',
            'consumer_key' => 'real-key',
            'consumer_secret' => 'real-secret',
            'supported_currencies' => 'UGX,KES',
        ];
    }

    public function test_super_admin_can_view_and_update_a_gateway_provider(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->get('/admin/gateway-providers');

        $response->assertOk();
        $response->assertSee('Pesapal');
        $response->assertSee('Yo Payments');

        $update = $this->actingAs($superAdmin)->put('/admin/gateway-providers/pesapal', $this->validPayload());

        $update->assertRedirect(route('admin.gateway-providers'));

        $pesapal = GatewayProvider::byProvider(PaymentProvider::Pesapal);
        $this->assertSame('active', $pesapal->status);
        $this->assertSame('production', $pesapal->environment);
        $this->assertSame('real-key', $pesapal->credentials['consumer_key']);
    }

    public function test_super_admin_can_update_yo_payments_with_its_own_field_set(): void
    {
        GatewayProvider::byProvider(PaymentProvider::YoPayments);

        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->put('/admin/gateway-providers/yo_payments', [
            'status' => 'active',
            'environment' => 'production',
            'api_username' => 'real-username',
            'api_password' => 'real-password',
            'supported_currencies' => 'UGX',
        ]);

        $response->assertRedirect(route('admin.gateway-providers'));

        $yoPayments = GatewayProvider::byProvider(PaymentProvider::YoPayments);
        $this->assertSame('real-username', $yoPayments->credentials['api_username']);
        $this->assertSame('real-password', $yoPayments->credentials['api_password']);
    }

    public function test_submitting_pesapal_fields_for_yo_payments_fails_validation(): void
    {
        GatewayProvider::byProvider(PaymentProvider::YoPayments);

        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->put('/admin/gateway-providers/yo_payments', $this->validPayload());

        $response->assertSessionHasErrors(['api_username', 'api_password']);
    }

    public function test_updating_credentials_preserves_other_stored_keys_like_a_cached_ipn_id(): void
    {
        $pesapal = GatewayProvider::byProvider(PaymentProvider::Pesapal);
        $pesapal->update(['credentials' => ['consumer_key' => 'old-key', 'consumer_secret' => 'old-secret', 'ipn_id' => 'cached-ipn-guid']]);

        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)->put('/admin/gateway-providers/pesapal', $this->validPayload())->assertRedirect();

        $pesapal->refresh();
        $this->assertSame('real-key', $pesapal->credentials['consumer_key']);
        $this->assertSame('cached-ipn-guid', $pesapal->credentials['ipn_id']);
    }

    public function test_a_gateway_provider_row_is_lazily_created_the_first_time_its_provider_is_visited(): void
    {
        $this->assertDatabaseCount('gateway_providers', 0);

        $superAdmin = User::factory()->superAdmin()->create();
        $this->actingAs($superAdmin)->get('/admin/gateway-providers')->assertOk();

        $this->assertDatabaseHas('gateway_providers', ['provider' => 'pesapal', 'status' => 'inactive']);
        $this->assertDatabaseHas('gateway_providers', ['provider' => 'yo_payments', 'status' => 'inactive']);
        $this->assertDatabaseCount('gateway_providers', 2);
    }

    public function test_business_admin_cannot_view_or_update_gateway_providers(): void
    {
        GatewayProvider::byProvider(PaymentProvider::Pesapal);

        $business = Business::factory()->create(['status' => 'approved']);
        $businessAdmin = User::factory()->businessAdmin($business)->create();

        $this->actingAs($businessAdmin)->get('/admin/gateway-providers')->assertForbidden();
        $this->actingAs($businessAdmin)->put('/admin/gateway-providers/pesapal', $this->validPayload())->assertForbidden();
    }

    public function test_guest_is_redirected_to_login_for_the_admin_screen(): void
    {
        $this->get('/admin/gateway-providers')->assertRedirect('/login');
    }
}
