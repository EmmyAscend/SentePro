<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\PaymentLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentLinkSharingTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_qr_code_endpoint_returns_a_scannable_svg_for_a_guest(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $paymentLink = PaymentLink::factory()->create(['business_id' => $business->id]);

        $response = $this->get("/pay/{$paymentLink->id}/qr-code");

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/svg+xml');
        $this->assertStringContainsString('<svg', $response->getContent());
    }

    public function test_the_payment_links_index_embeds_the_qr_code_and_html_snippet_for_each_link(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $paymentLink = PaymentLink::factory()->create(['business_id' => $business->id, 'title' => 'Sunday Offering']);
        $admin = User::factory()->businessAdmin($business)->create();

        $response = $this->actingAs($admin)->get('/payment-links');

        $response->assertOk();
        $response->assertSee(route('checkout.qr-code', $paymentLink), false);
        $response->assertSee(route('checkout.show', $paymentLink), false);
        $response->assertSee('Pay Sunday Offering', false);
    }

    public function test_the_create_link_business_dropdown_lists_every_business_not_just_those_with_links(): void
    {
        $businessWithLink = Business::factory()->create(['status' => 'approved', 'business_name' => 'Has A Link Ltd']);
        $businessWithoutLink = Business::factory()->create(['status' => 'approved', 'business_name' => 'No Links Yet Ltd']);
        PaymentLink::factory()->create(['business_id' => $businessWithLink->id]);

        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->get('/payment-links');

        $response->assertOk();
        $response->assertSee('Has A Link Ltd');
        $response->assertSee('No Links Yet Ltd');
    }
}
