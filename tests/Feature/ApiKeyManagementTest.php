<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiKeyManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_admin_can_create_and_list_an_api_key(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $admin = User::factory()->businessAdmin($business)->create();

        $response = $this->actingAs($admin)->post('/api-keys', ['name' => 'Production backend']);

        $response->assertRedirect(route('api-keys.index'));
        $response->assertSessionHas('plainTextToken');

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_type' => User::class,
            'tokenable_id' => $admin->id,
            'name' => 'Production backend',
        ]);

        $indexResponse = $this->actingAs($admin)->get('/api-keys');
        $indexResponse->assertOk();
        $indexResponse->assertSee('Production backend');
    }

    public function test_staff_cannot_manage_api_keys(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $staff = User::factory()->staff($business)->create();

        $this->actingAs($staff)->get('/api-keys')->assertForbidden();
        $this->actingAs($staff)->post('/api-keys', ['name' => 'Sneaky key'])->assertForbidden();
    }

    public function test_business_admin_can_revoke_an_api_key(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $admin = User::factory()->businessAdmin($business)->create();
        $token = $admin->createToken('To be revoked');

        $response = $this->actingAs($admin)->delete("/api-keys/{$token->accessToken->id}");

        $response->assertRedirect(route('api-keys.index'));
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $token->accessToken->id]);
    }

    public function test_a_revoked_token_can_no_longer_authenticate(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $admin = User::factory()->businessAdmin($business)->create();
        $token = $admin->createToken('Short lived');
        $plainTextToken = $token->plainTextToken;

        $token->accessToken->delete();

        $response = $this->withHeaders(['Authorization' => "Bearer {$plainTextToken}"])
            ->getJson('/api/v1/wallet');

        $response->assertUnauthorized();
    }

    public function test_a_business_admin_cannot_revoke_another_users_token(): void
    {
        $businessA = Business::factory()->create(['status' => 'approved']);
        $businessB = Business::factory()->create(['status' => 'approved']);
        $adminA = User::factory()->businessAdmin($businessA)->create();
        $adminB = User::factory()->businessAdmin($businessB)->create();
        $token = $adminA->createToken('Business A key');

        $this->actingAs($adminB)->delete("/api-keys/{$token->accessToken->id}")->assertRedirect();

        $this->assertDatabaseHas('personal_access_tokens', ['id' => $token->accessToken->id]);
    }
}
