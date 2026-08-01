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

    private function gatewayProvider(Business $business): GatewayProvider
    {
        return GatewayProvider::create([
            'business_id' => $business->id,
            'name' => 'Pesapal Cards',
            'provider' => 'pesapal',
            'status' => 'active',
            'environment' => 'sandbox',
            'webhook_url' => 'https://example.test/webhooks/pesapal/1',
            'credentials' => ['consumer_key' => 'test-key', 'consumer_secret' => 'test-secret'],
            'supported_countries' => 'UG',
            'supported_currencies' => 'UGX',
        ]);
    }

    public function test_business_admin_can_test_their_own_gateways_connection(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $gatewayProvider = $this->gatewayProvider($business);
        $admin = User::factory()->businessAdmin($business)->create();

        Http::fake([
            '*/api/Auth/RequestToken' => Http::response(['token' => 'fake-token', 'status' => '200']),
        ]);

        $response = $this->actingAs($admin)->post("/gateways/{$gatewayProvider->id}/test");

        $response->assertRedirect(route('gateways.index'));
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
        $business = Business::factory()->create(['status' => 'approved']);
        $gatewayProvider = $this->gatewayProvider($business);
        $admin = User::factory()->businessAdmin($business)->create();

        Http::fake([
            '*/api/Auth/RequestToken' => Http::response(['error' => 'invalid_client'], 401),
        ]);

        $response = $this->actingAs($admin)->post("/gateways/{$gatewayProvider->id}/test");

        $response->assertRedirect(route('gateways.index'));
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

    public function test_a_business_admin_cannot_test_another_businesss_gateway(): void
    {
        $businessA = Business::factory()->create(['status' => 'approved']);
        $businessB = Business::factory()->create(['status' => 'approved']);
        $gatewayProvider = $this->gatewayProvider($businessB);
        $adminA = User::factory()->businessAdmin($businessA)->create();

        // GatewayProvider is tenant-scoped, so route-model-binding can't even
        // find another business's provider for this user — 404, same pattern
        // as every other tenant-scoped resource in this app.
        $this->actingAs($adminA)->post("/gateways/{$gatewayProvider->id}/test")->assertNotFound();
    }

    public function test_super_admin_can_view_the_gateway_monitoring_dashboard(): void
    {
        $business = Business::factory()->create(['status' => 'approved', 'business_name' => 'Monitored Biz']);
        $gatewayProvider = $this->gatewayProvider($business);
        $gatewayProvider->update([
            'last_checked_at' => now(),
            'last_health_status' => 'healthy',
            'last_latency_ms' => 120,
        ]);
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->get('/admin/gateway-monitoring');

        $response->assertOk();
        $response->assertSee('Monitored Biz');
        $response->assertSee('HEALTHY');
    }

    public function test_business_admin_cannot_view_the_gateway_monitoring_dashboard(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $admin = User::factory()->businessAdmin($business)->create();

        $this->actingAs($admin)->get('/admin/gateway-monitoring')->assertForbidden();
    }
}
