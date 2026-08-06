<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\PaymentLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

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
}
