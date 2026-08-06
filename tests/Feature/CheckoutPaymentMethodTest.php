<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\GatewayProvider;
use App\Models\PaymentLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CheckoutPaymentMethodTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_shows_card_and_mobile_money_choices_never_the_provider_names(): void
    {
        GatewayProvider::create(['provider' => 'pesapal', 'status' => 'active', 'environment' => 'sandbox', 'webhook_url' => 'https://example.test/webhooks/pesapal', 'credentials' => [], 'supported_currencies' => 'UGX']);
        GatewayProvider::create(['provider' => 'yo_payments', 'status' => 'active', 'environment' => 'sandbox', 'webhook_url' => 'https://example.test/webhooks/yo-payments/success', 'credentials' => [], 'supported_currencies' => 'UGX']);

        $business = Business::factory()->create(['status' => 'approved']);
        $paymentLink = PaymentLink::factory()->create(['business_id' => $business->id]);

        $response = $this->get('/pay/'.$paymentLink->id);

        $response->assertOk();
        $response->assertSee('Debit or Credit Card');
        $response->assertSee('Mobile Money (MTN / Airtel)');
        $response->assertDontSee('Pesapal');
        $response->assertDontSee('Yo Payments');
    }

    public function test_checkout_hides_a_payment_method_whose_provider_is_inactive(): void
    {
        GatewayProvider::create(['provider' => 'pesapal', 'status' => 'inactive', 'environment' => 'sandbox', 'webhook_url' => 'https://example.test/webhooks/pesapal', 'credentials' => [], 'supported_currencies' => 'UGX']);
        GatewayProvider::create(['provider' => 'yo_payments', 'status' => 'active', 'environment' => 'sandbox', 'webhook_url' => 'https://example.test/webhooks/yo-payments/success', 'credentials' => [], 'supported_currencies' => 'UGX']);

        $business = Business::factory()->create(['status' => 'approved']);
        $paymentLink = PaymentLink::factory()->create(['business_id' => $business->id]);

        $response = $this->get('/pay/'.$paymentLink->id);

        $response->assertOk();
        $response->assertDontSee('Debit or Credit Card');
        $response->assertSee('Mobile Money (MTN / Airtel)');
    }

    public function test_two_different_businesses_payment_links_both_route_through_the_same_platform_wide_pesapal_credentials(): void
    {
        GatewayProvider::create([
            'provider' => 'pesapal',
            'status' => 'active',
            'environment' => 'sandbox',
            'webhook_url' => 'https://example.test/webhooks/pesapal',
            'credentials' => ['consumer_key' => 'shared-key', 'consumer_secret' => 'shared-secret'],
            'supported_currencies' => 'UGX',
        ]);

        $businessA = Business::factory()->create(['status' => 'approved']);
        $businessB = Business::factory()->create(['status' => 'approved']);
        $linkA = PaymentLink::factory()->create(['business_id' => $businessA->id, 'amount' => 1000]);
        $linkB = PaymentLink::factory()->create(['business_id' => $businessB->id, 'amount' => 2000]);

        Http::fake([
            '*/api/Auth/RequestToken' => Http::response(['token' => 'fake-token', 'status' => '200']),
            '*/api/URLSetup/RegisterIPN' => Http::response(['ipn_id' => 'ipn-guid-shared', 'status' => '200']),
            '*/api/Transactions/SubmitOrderRequest' => Http::response([
                'order_tracking_id' => 'tracking-shared',
                'redirect_url' => 'https://cybqa.pesapal.com/pesapalv3/checkout/shared',
                'status' => '200',
            ]),
        ]);

        foreach ([$linkA, $linkB] as $link) {
            $this->post('/pay/'.$link->id, [
                'customer_name' => 'Jane Doe',
                'customer_email' => 'jane@example.test',
                'currency' => 'UGX',
                'payment_method' => 'card',
            ])->assertRedirect('https://cybqa.pesapal.com/pesapalv3/checkout/shared');
        }

        $this->assertDatabaseHas('payment_transactions', ['business_id' => $businessA->id, 'provider' => 'pesapal']);
        $this->assertDatabaseHas('payment_transactions', ['business_id' => $businessB->id, 'provider' => 'pesapal']);
        $this->assertDatabaseCount('gateway_providers', 1);
    }
}
