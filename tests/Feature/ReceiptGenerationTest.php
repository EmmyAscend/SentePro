<?php

namespace Tests\Feature;

use App\Mail\PaymentReceivedMail;
use App\Mail\ReceiptMail;
use App\Models\Business;
use App\Models\GatewayProvider;
use App\Models\PaymentLink;
use App\Models\PaymentTransaction;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ReceiptGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_completed_transaction_auto_generates_a_receipt_and_queues_an_email(): void
    {
        Mail::fake();

        $business = Business::factory()->create(['status' => 'approved']);
        $admin = User::factory()->businessAdmin($business)->create();
        $gatewayProvider = GatewayProvider::create([
            'business_id' => $business->id,
            'name' => 'Pesapal Cards',
            'provider' => 'pesapal',
            'status' => 'active',
            'environment' => 'sandbox',
            'webhook_url' => 'https://example.test/webhooks/pesapal/1',
            'credentials' => ['consumer_key' => 'test', 'consumer_secret' => 'test'],
            'supported_countries' => 'UG',
            'supported_currencies' => 'UGX',
        ]);
        $paymentLink = PaymentLink::factory()->create(['business_id' => $business->id, 'amount' => 5000]);

        $transaction = PaymentTransaction::create([
            'business_id' => $business->id,
            'payment_link_id' => $paymentLink->id,
            'provider' => 'pesapal',
            'amount' => 5000,
            'currency' => 'UGX',
            'status' => 'processing',
            'external_reference' => 'txn-receipt-1',
            'provider_reference' => 'tracking-receipt-1',
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.test',
        ]);

        Http::fake([
            '*/api/Auth/RequestToken' => Http::response(['token' => 'fake-token', 'status' => '200']),
            '*/api/Transactions/GetTransactionStatus*' => Http::response([
                'payment_status_code' => 1,
                'confirmation_code' => 'CONF1',
                'status' => '200',
            ]),
        ]);

        $this->post("/webhooks/pesapal/{$gatewayProvider->id}", [
            'OrderTrackingId' => 'tracking-receipt-1',
            'OrderMerchantReference' => 'txn-receipt-1',
        ])->assertOk();

        $receipt = Receipt::where('payment_transaction_id', $transaction->id)->first();

        $this->assertNotNull($receipt);
        $this->assertSame($business->id, $receipt->business_id);
        $this->assertSame('5000.00', (string) $receipt->amount);
        $this->assertSame('5000.00', (string) $receipt->net_amount); // no fee structure configured
        $this->assertSame('Jane Doe', $receipt->customer_name);
        $this->assertNotNull($receipt->emailed_at);
        $this->assertStringStartsWith('RCPT-', $receipt->reference_number);

        Mail::assertQueued(ReceiptMail::class, fn ($mail) => $mail->receipt->is($receipt)
            && $mail->hasTo('jane@example.test'));

        Mail::assertQueued(PaymentReceivedMail::class, fn ($mail) => $mail->receipt->is($receipt)
            && $mail->hasTo($admin->email));
    }

    public function test_the_public_receipt_page_is_viewable_by_a_guest(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $transaction = PaymentTransaction::factory()->create(['business_id' => $business->id]);

        $receipt = Receipt::create([
            'business_id' => $business->id,
            'payment_transaction_id' => $transaction->id,
            'reference_number' => 'RCPT-TESTGUEST1',
            'amount' => 2500,
            'net_amount' => 2400,
            'currency' => 'UGX',
            'customer_name' => 'Jane Doe',
        ]);

        $response = $this->get('/receipts/RCPT-TESTGUEST1');

        $response->assertOk();
        $response->assertSee($business->business_name);
        $response->assertSee('RCPT-TESTGUEST1');
    }

    public function test_the_public_verification_page_is_viewable_by_a_guest(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $transaction = PaymentTransaction::factory()->create(['business_id' => $business->id]);

        $receipt = Receipt::create([
            'business_id' => $business->id,
            'payment_transaction_id' => $transaction->id,
            'reference_number' => 'RCPT-TESTVERIFY',
            'amount' => 2500,
            'net_amount' => 2400,
            'currency' => 'UGX',
        ]);

        $response = $this->get('/receipts/RCPT-TESTVERIFY/verify');

        $response->assertOk();
        $response->assertSee('genuine');
    }

    public function test_the_qr_code_endpoint_returns_a_scannable_svg_for_a_guest(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $transaction = PaymentTransaction::factory()->create(['business_id' => $business->id]);

        $receipt = Receipt::create([
            'business_id' => $business->id,
            'payment_transaction_id' => $transaction->id,
            'reference_number' => 'RCPT-TESTQRCODE',
            'amount' => 2500,
            'net_amount' => 2400,
            'currency' => 'UGX',
        ]);

        $response = $this->get('/receipts/RCPT-TESTQRCODE/qr-code');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/svg+xml');
        $this->assertStringContainsString('<svg', $response->getContent());
    }

    public function test_the_public_receipt_page_embeds_the_qr_code_image(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $transaction = PaymentTransaction::factory()->create(['business_id' => $business->id]);

        $receipt = Receipt::create([
            'business_id' => $business->id,
            'payment_transaction_id' => $transaction->id,
            'reference_number' => 'RCPT-TESTQREMBED',
            'amount' => 2500,
            'net_amount' => 2400,
            'currency' => 'UGX',
        ]);

        $response = $this->get('/receipts/RCPT-TESTQREMBED');

        $response->assertOk();
        $response->assertSee(route('receipts.qr-code', $receipt), false);
    }

    public function test_business_admin_only_sees_their_own_receipts_in_the_index(): void
    {
        $businessA = Business::factory()->create(['status' => 'approved']);
        $businessB = Business::factory()->create(['status' => 'approved']);

        $transactionA = PaymentTransaction::factory()->create(['business_id' => $businessA->id]);
        $transactionB = PaymentTransaction::factory()->create(['business_id' => $businessB->id]);

        Receipt::create([
            'business_id' => $businessA->id,
            'payment_transaction_id' => $transactionA->id,
            'reference_number' => 'RCPT-BUSINESSA01',
            'amount' => 1000,
            'net_amount' => 950,
            'currency' => 'UGX',
        ]);

        Receipt::create([
            'business_id' => $businessB->id,
            'payment_transaction_id' => $transactionB->id,
            'reference_number' => 'RCPT-BUSINESSB01',
            'amount' => 2000,
            'net_amount' => 1900,
            'currency' => 'UGX',
        ]);

        $adminA = User::factory()->businessAdmin($businessA)->create();

        $response = $this->actingAs($adminA)->get('/receipts');

        $response->assertOk();
        $response->assertSee('RCPT-BUSINESSA01');
        $response->assertDontSee('RCPT-BUSINESSB01');
    }
}
