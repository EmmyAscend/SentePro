<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\PaymentLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicCheckoutWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_payment_link_page_is_available_for_a_business_link(): void
    {
        $business = Business::factory()->create([
            'business_name' => 'Public Checkout Business',
            'status' => 'approved',
        ]);

        $paymentLink = PaymentLink::factory()->create([
            'business_id' => $business->id,
            'title' => 'Community Support Fund',
            'type' => 'donation',
            'amount' => 2500,
            'status' => 'active',
        ]);

        $response = $this->get('/pay/'.$paymentLink->id);

        $response->assertOk();
        $response->assertSee('Community Support Fund');
    }
}
