<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\GatewayProvider;
use App\Models\PaymentLink;
use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaymentLinkCustomFieldsTest extends TestCase
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

    public function test_creating_a_payment_link_with_fields_stores_standard_and_custom_field_definitions(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $admin = User::factory()->businessAdmin($business)->create();

        $this->actingAs($admin)->post('/payment-links', [
            'title' => 'School Fees',
            'type' => 'invoice',
            'amount' => 50000,
            'custom_amount' => 0,
            'expiry_date' => now()->addDays(30)->toDateString(),
            'standard_fields' => ['student_id', 'invoice_number'],
            'custom_field_labels' => "T-Shirt Size\nHouse Color",
        ])->assertRedirect();

        $paymentLink = PaymentLink::where('title', 'School Fees')->firstOrFail();

        $this->assertEquals([
            ['key' => 'student_id', 'label' => 'Student ID'],
            ['key' => 'invoice_number', 'label' => 'Invoice Number'],
            ['key' => 't_shirt_size', 'label' => 'T-Shirt Size'],
            ['key' => 'house_color', 'label' => 'House Color'],
        ], $paymentLink->fields);
    }

    public function test_the_checkout_page_renders_an_input_for_each_configured_field(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $paymentLink = PaymentLink::factory()->create([
            'business_id' => $business->id,
            'fields' => [['key' => 'product', 'label' => 'Product'], ['key' => 'quantity', 'label' => 'Quantity']],
        ]);

        $response = $this->get('/pay/'.$paymentLink->id);

        $response->assertOk();
        $response->assertSee('Product');
        $response->assertSee('name="custom_fields[product]"', false);
        $response->assertSee('name="custom_fields[quantity]"', false);
    }

    public function test_submitting_checkout_with_field_values_captures_label_and_value_on_the_transaction(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $this->gatewayProvider();
        $paymentLink = PaymentLink::factory()->create([
            'business_id' => $business->id,
            'amount' => 2500,
            'fields' => [['key' => 'product', 'label' => 'Product'], ['key' => 'quantity', 'label' => 'Quantity']],
        ]);

        Http::fake([
            '*/api/Auth/RequestToken' => Http::response(['token' => 'fake-token', 'status' => '200']),
            '*/api/URLSetup/RegisterIPN' => Http::response(['ipn_id' => 'ipn-guid-1', 'status' => '200']),
            '*/api/Transactions/SubmitOrderRequest' => Http::response([
                'order_tracking_id' => 'tracking-fields-1',
                'redirect_url' => 'https://cybqa.pesapal.com/pesapalv3/checkout/fields1',
                'status' => '200',
            ]),
        ]);

        $this->post('/pay/'.$paymentLink->id, [
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.test',
            'currency' => 'UGX',
            'payment_method' => 'card',
            'custom_fields' => ['product' => 'T-Shirt', 'quantity' => '2'],
        ])->assertRedirect('https://cybqa.pesapal.com/pesapalv3/checkout/fields1');

        $transaction = PaymentTransaction::where('business_id', $business->id)->firstOrFail();

        $this->assertSame([
            'product' => ['label' => 'Product', 'value' => 'T-Shirt'],
            'quantity' => ['label' => 'Quantity', 'value' => '2'],
        ], $transaction->custom_field_values);
    }

    public function test_submitting_checkout_with_configured_but_blank_fields_still_succeeds(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $this->gatewayProvider();
        $paymentLink = PaymentLink::factory()->create([
            'business_id' => $business->id,
            'amount' => 2500,
            'fields' => [['key' => 'comment', 'label' => 'Comment']],
        ]);

        Http::fake([
            '*/api/Auth/RequestToken' => Http::response(['token' => 'fake-token', 'status' => '200']),
            '*/api/URLSetup/RegisterIPN' => Http::response(['ipn_id' => 'ipn-guid-2', 'status' => '200']),
            '*/api/Transactions/SubmitOrderRequest' => Http::response([
                'order_tracking_id' => 'tracking-fields-2',
                'redirect_url' => 'https://cybqa.pesapal.com/pesapalv3/checkout/fields2',
                'status' => '200',
            ]),
        ]);

        $this->post('/pay/'.$paymentLink->id, [
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.test',
            'currency' => 'UGX',
            'payment_method' => 'card',
        ])->assertRedirect('https://cybqa.pesapal.com/pesapalv3/checkout/fields2');

        $transaction = PaymentTransaction::where('business_id', $business->id)->firstOrFail();

        $this->assertSame([], $transaction->custom_field_values);
    }

    public function test_the_transaction_ledger_displays_captured_field_values(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $admin = User::factory()->businessAdmin($business)->create();
        PaymentTransaction::factory()->create([
            'business_id' => $business->id,
            'external_reference' => 'txn-fields-ledger-1',
            'custom_field_values' => ['product' => ['label' => 'Product', 'value' => 'T-Shirt']],
        ]);

        $response = $this->actingAs($admin)->get('/transactions');

        $response->assertOk();
        $response->assertSee('Product: T-Shirt');
    }
}
