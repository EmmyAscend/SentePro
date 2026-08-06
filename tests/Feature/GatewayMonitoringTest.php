<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\GatewayProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GatewayMonitoringTest extends TestCase
{
    use RefreshDatabase;

    private function gatewayProvider(): GatewayProvider
    {
        return GatewayProvider::create([
            'provider' => 'pesapal',
            'status' => 'active',
            'environment' => 'sandbox',
            'webhook_url' => 'https://example.test/webhooks/pesapal',
            'credentials' => ['consumer_key' => 'test-key', 'consumer_secret' => 'test-secret'],
            'supported_currencies' => 'UGX',
        ]);
    }

    public function test_super_admin_can_test_a_gateways_connection(): void
    {
        $gatewayProvider = $this->gatewayProvider();
        $superAdmin = User::factory()->superAdmin()->create();

        Http::fake([
            '*/api/Auth/RequestToken' => Http::response(['token' => 'fake-token', 'status' => '200']),
        ]);

        $response = $this->actingAs($superAdmin)->post("/admin/gateway-providers/{$gatewayProvider->provider->value}/test");

        $response->assertRedirect(route('admin.gateway-providers'));
        $response->assertSessionHas('status', 'Connection healthy.');

        $this->assertDatabaseHas('gateway_logs', [
            'gateway_provider_id' => $gatewayProvider->id,
            'method' => 'ping',
            'success' => 1,
        ]);

        $gatewayProvider->refresh();
        $this->assertSame('healthy', $gatewayProvider->last_health_status);
        $this->assertNotNull($gatewayProvider->last_checked_at);
        $this->assertNotNull($gatewayProvider->last_latency_ms);
    }

    public function test_a_failed_connection_test_records_unhealthy_status_without_a_server_error(): void
    {
        $gatewayProvider = $this->gatewayProvider();
        $superAdmin = User::factory()->superAdmin()->create();

        Http::fake([
            '*/api/Auth/RequestToken' => Http::response(['error' => 'invalid_client'], 401),
        ]);

        $response = $this->actingAs($superAdmin)->post("/admin/gateway-providers/{$gatewayProvider->provider->value}/test");

        $response->assertRedirect(route('admin.gateway-providers'));
        $response->assertSessionHas('status', fn ($status) => str_starts_with($status, 'Connection failed:'));

        $gatewayProvider->refresh();
        $this->assertSame('unhealthy', $gatewayProvider->last_health_status);
        $this->assertNotNull($gatewayProvider->last_error);

        $this->assertDatabaseHas('gateway_logs', [
            'gateway_provider_id' => $gatewayProvider->id,
            'method' => 'ping',
            'success' => 0,
        ]);
    }

    public function test_business_admin_cannot_test_a_gateways_connection(): void
    {
        $gatewayProvider = $this->gatewayProvider();
        $business = Business::factory()->create(['status' => 'approved']);
        $admin = User::factory()->businessAdmin($business)->create();

        $this->actingAs($admin)->post("/admin/gateway-providers/{$gatewayProvider->provider->value}/test")->assertForbidden();
    }

    public function test_super_admin_can_view_the_gateway_monitoring_dashboard(): void
    {
        $gatewayProvider = $this->gatewayProvider();
        $gatewayProvider->update([
            'last_checked_at' => now(),
            'last_health_status' => 'healthy',
            'last_latency_ms' => 120,
        ]);
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->get('/admin/gateway-monitoring');

        $response->assertOk();
        $response->assertSee('Pesapal');
        $response->assertSee('HEALTHY');
    }

    public function test_business_admin_cannot_view_the_gateway_monitoring_dashboard(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $admin = User::factory()->businessAdmin($business)->create();

        $this->actingAs($admin)->get('/admin/gateway-monitoring')->assertForbidden();
    }
}
