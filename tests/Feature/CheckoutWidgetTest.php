<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\PaymentLink;
use App\Models\PaymentTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_checkout_widget_script_file_exists_and_defines_the_expected_trigger_attribute(): void
    {
        $this->assertFileExists(public_path('sentepro-checkout.js'));

        $contents = file_get_contents(public_path('sentepro-checkout.js'));

        $this->assertStringContainsString('data-sentepro-checkout', $contents);
        $this->assertStringContainsString('data-checkout-url', $contents);
        $this->assertStringContainsString("addEventListener('message'", $contents);
    }

    public function test_the_checkout_status_page_includes_a_postmessage_script_for_the_widget(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $paymentLink = PaymentLink::factory()->create(['business_id' => $business->id]);
        $transaction = PaymentTransaction::factory()->create([
            'business_id' => $business->id,
            'payment_link_id' => $paymentLink->id,
            'status' => 'completed',
            'external_reference' => 'txn-widget-1',
        ]);

        $response = $this->get('/pay/'.$paymentLink->id.'/status');

        $response->assertOk();
        $response->assertSee('sentepro-checkout', false);
        $response->assertSee('postMessage', false);
        $response->assertSee('txn-widget-1', false);
    }

    public function test_the_checkout_status_page_renders_without_a_widget_script_when_no_transaction_exists_yet(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $paymentLink = PaymentLink::factory()->create(['business_id' => $business->id]);

        $response = $this->get('/pay/'.$paymentLink->id.'/status');

        $response->assertOk();
        $response->assertDontSee('postMessage', false);
    }
}
