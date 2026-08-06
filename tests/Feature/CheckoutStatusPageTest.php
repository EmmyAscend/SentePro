<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\PaymentLink;
use App\Models\PaymentTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutStatusPageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The actual reported bug: the heading and subtext were hardcoded to
     * "Payment request received... is now being processed" regardless of
     * the transaction's real status — only the small "Status: X" line ever
     * reflected reality. A customer reloading the status page after their
     * payment had already completed (the exact scenario reported) saw a
     * contradiction: "Status: Completed" sitting right below a heading
     * that still said the payment was being processed.
     */
    public function test_a_completed_transaction_shows_a_success_heading_not_the_generic_processing_text(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $paymentLink = PaymentLink::factory()->create(['business_id' => $business->id]);
        PaymentTransaction::factory()->create([
            'business_id' => $business->id,
            'payment_link_id' => $paymentLink->id,
            'status' => 'completed',
            'external_reference' => 'txn-status-page-1',
        ]);

        $response = $this->get('/pay/'.$paymentLink->id.'/status');

        $response->assertOk();
        $response->assertSee('Payment successful');
        $response->assertSee('Your payment has been confirmed. Thank you!');
        $response->assertDontSee('is now being processed');
        // The checkmark icon container is visible (not hidden) for a completed transaction.
        $response->assertDontSee('id="status-icon-completed" class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-lime-400/20 text-lime-300 hidden"', false);
    }

    public function test_a_failed_transaction_shows_a_failure_heading(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $paymentLink = PaymentLink::factory()->create(['business_id' => $business->id]);
        PaymentTransaction::factory()->create([
            'business_id' => $business->id,
            'payment_link_id' => $paymentLink->id,
            'status' => 'failed',
            'external_reference' => 'txn-status-page-2',
        ]);

        $response = $this->get('/pay/'.$paymentLink->id.'/status');

        $response->assertOk();
        $response->assertSee('Payment failed');
        $response->assertSee('This payment could not be completed. You can go back and try again.');
    }

    public function test_a_still_processing_transaction_keeps_the_original_captured_text(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $paymentLink = PaymentLink::factory()->create(['business_id' => $business->id, 'title' => 'Processing Test']);
        PaymentTransaction::factory()->create([
            'business_id' => $business->id,
            'payment_link_id' => $paymentLink->id,
            'status' => 'processing',
            'external_reference' => 'txn-status-page-3',
        ]);

        $response = $this->get('/pay/'.$paymentLink->id.'/status');

        $response->assertOk();
        $response->assertSee('Payment request received');
        $response->assertSee('Your payment intent for Processing Test has been captured and is now being processed.');
    }

    public function test_the_status_page_offers_a_copy_button_for_the_reference_and_a_screenshot_hint(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $paymentLink = PaymentLink::factory()->create(['business_id' => $business->id]);
        PaymentTransaction::factory()->create([
            'business_id' => $business->id,
            'payment_link_id' => $paymentLink->id,
            'status' => 'completed',
            'external_reference' => 'txn-status-page-4',
        ]);

        $response = $this->get('/pay/'.$paymentLink->id.'/status');

        $response->assertOk();
        $response->assertSee("navigator.clipboard.writeText('txn-status-page-4')", false);
        $response->assertSee('Copy');
        $response->assertSee('take a screenshot of this page to keep as proof of payment');
    }

    public function test_the_status_page_shows_the_sentepro_brand_mark(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $paymentLink = PaymentLink::factory()->create(['business_id' => $business->id]);

        $response = $this->get('/pay/'.$paymentLink->id.'/status');

        $response->assertOk();
        $response->assertSee('font-pacifico text-lime-400', false);
    }
}
