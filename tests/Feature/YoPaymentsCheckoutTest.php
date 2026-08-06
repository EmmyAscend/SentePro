<?php

namespace Tests\Feature;

use App\Enums\PaymentProvider;
use App\Models\Business;
use App\Models\GatewayProvider;
use App\Models\PaymentLink;
use App\Models\PaymentTransaction;
use App\Services\PaymentGatewayManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class YoPaymentsCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function gatewayProvider(): GatewayProvider
    {
        return GatewayProvider::create([
            'provider' => 'yo_payments',
            'status' => 'active',
            'environment' => 'sandbox',
            'webhook_url' => 'https://example.test/webhooks/yo-payments/success',
            'credentials' => ['api_username' => 'test-user', 'api_password' => 'test-pass'],
            'supported_currencies' => 'UGX',
        ]);
    }

    private function xmlResponse(array $fields): string
    {
        $body = '<Response>';
        foreach ($fields as $key => $value) {
            $body .= "<{$key}>{$value}</{$key}>";
        }
        $body .= '</Response>';

        return '<?xml version="1.0" encoding="UTF-8"?><AutoCreate>'.$body.'</AutoCreate>';
    }

    public function test_checkout_requires_a_phone_number_for_mobile_money(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $this->gatewayProvider();
        $paymentLink = PaymentLink::factory()->create(['business_id' => $business->id, 'amount' => 5000]);

        $response = $this->post('/pay/'.$paymentLink->id, [
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.test',
            'payment_method' => 'mobile_money',
        ]);

        $response->assertSessionHasErrors('customer_phone');
        $this->assertDatabaseMissing('payment_transactions', ['business_id' => $business->id]);
    }

    public function test_checkout_pushes_a_mobile_money_prompt_and_lands_on_the_status_page(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $gatewayProvider = $this->gatewayProvider();
        $paymentLink = PaymentLink::factory()->create(['business_id' => $business->id, 'amount' => 5000]);

        Http::fake([
            '*' => Http::response($this->xmlResponse([
                'Status' => 'OK',
                'StatusCode' => 0,
                'TransactionStatus' => 'PENDING',
                'TransactionReference' => 'yo-txn-ref-123',
            ]), 200, ['Content-Type' => 'text/xml']),
        ]);

        $response = $this->post('/pay/'.$paymentLink->id, [
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.test',
            'customer_phone_country' => '256',
            'customer_phone_local' => '712345678',
            'payment_method' => 'mobile_money',
        ]);

        // No hosted checkout page for mobile money — the prompt goes straight
        // to the customer's phone, so this redirects to our own status page.
        $response->assertRedirect('/pay/'.$paymentLink->id.'/status');

        $this->assertDatabaseHas('payment_transactions', [
            'business_id' => $business->id,
            'payment_link_id' => $paymentLink->id,
            'provider' => 'yo_payments',
            'status' => 'processing',
            'provider_reference' => 'yo-txn-ref-123',
            'customer_phone' => '256712345678',
        ]);

        Http::assertSent(function ($request) {
            return str_contains($request->body(), '<Method>acdepositfunds</Method>')
                && str_contains($request->body(), '<Account>256712345678</Account>');
        });

        $this->assertDatabaseHas('gateway_logs', [
            'gateway_provider_id' => $gatewayProvider->id,
            'method' => 'initiate',
            'success' => 1,
        ]);
        $this->assertSame('healthy', $gatewayProvider->fresh()->last_health_status);
    }

    /**
     * The checkout form's country-code picker + 9-digit local-number field
     * (PublicCheckoutController::store()) replaced the old free-text phone
     * input this test used to exercise leading-zero normalization against —
     * a leading zero is no longer reachable through this form at all, since
     * the local field is validated to exactly 9 digits. The controller
     * combines the two into a single well-formed international string
     * before it ever reaches PaymentInitiationService; PhoneNumber::
     * normalizeUganda() (still exercised directly in
     * tests/Unit/PhoneNumberTest.php) is now a defensive no-op for numbers
     * built this way, kept as a safety net for other entry paths.
     */
    public function test_the_country_code_and_local_number_are_combined_before_reaching_yo_payments(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $this->gatewayProvider();
        $paymentLink = PaymentLink::factory()->create(['business_id' => $business->id, 'amount' => 5000]);

        Http::fake([
            '*' => Http::response($this->xmlResponse([
                'Status' => 'OK',
                'StatusCode' => 0,
                'TransactionStatus' => 'PENDING',
                'TransactionReference' => 'yo-txn-ref-local',
            ]), 200, ['Content-Type' => 'text/xml']),
        ]);

        $this->post('/pay/'.$paymentLink->id, [
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.test',
            'customer_phone_country' => '256',
            'customer_phone_local' => '712345678',
            'payment_method' => 'mobile_money',
        ])->assertRedirect('/pay/'.$paymentLink->id.'/status');

        $this->assertDatabaseHas('payment_transactions', [
            'payment_link_id' => $paymentLink->id,
            'customer_phone' => '256712345678',
        ]);

        Http::assertSent(function ($request) {
            return str_contains($request->body(), '<Method>acdepositfunds</Method>')
                && str_contains($request->body(), '<Account>256712345678</Account>');
        });
    }

    public function test_yo_payments_webhook_reconciles_status_and_credits_the_wallet_on_completion(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $this->gatewayProvider();
        $paymentLink = PaymentLink::factory()->create(['business_id' => $business->id, 'amount' => 5000]);

        $transaction = PaymentTransaction::create([
            'business_id' => $business->id,
            'payment_link_id' => $paymentLink->id,
            'provider' => 'yo_payments',
            'amount' => 5000,
            'currency' => 'UGX',
            'status' => 'processing',
            'external_reference' => 'txn-ref-yo-1',
            'provider_reference' => 'yo-txn-ref-123',
            'customer_phone' => '256712345678',
        ]);

        Http::fake([
            '*' => Http::response($this->xmlResponse([
                'Status' => 'OK',
                'StatusCode' => 0,
                'TransactionStatus' => 'SUCCEEDED',
                'TransactionReference' => 'yo-txn-ref-123',
            ]), 200, ['Content-Type' => 'text/xml']),
        ]);

        // Yo's callback signature scheme isn't confidently verifiable (see
        // YoPaymentsWebhookController docblock), so this only ever finds the
        // transaction from the payload — the completed/failed determination
        // still comes from actransactioncheckstatus, not the payload itself.
        $response = $this->post('/webhooks/yo-payments/success', [
            'external_ref' => 'txn-ref-yo-1',
            'network_ref' => 'yo-txn-ref-123',
            'amount' => 5000,
        ]);

        $response->assertOk();
        $this->assertSame('completed', $transaction->fresh()->status->value);

        $wallet = $business->wallet->fresh();
        $this->assertSame('5000.00', (string) $wallet->available_balance);
        $this->assertSame('5000.00', (string) $wallet->settlement_balance);
    }

    /**
     * The actual reported "payment approved on phone, status never
     * advances past Processing" bug: our acdepositfunds + NonBlocking=TRUE
     * flow (an on-screen/USSD prompt the customer approves) is Yo's own
     * documented "Pull Method" (API spec section 6.1) — but
     * actransactioncheckstatus was sending DepositTransactionType=PUSH,
     * which refers to a completely different, deprecated mechanism
     * (section 6.2). Yo's real API responds "transaction not found" to
     * every such check regardless of the transaction's actual status,
     * which looks identical to "still processing" from our side.
     */
    public function test_check_status_asks_yo_payments_about_a_pull_transaction_not_a_push_one(): void
    {
        $this->gatewayProvider();

        Http::fake([
            '*' => Http::response($this->xmlResponse([
                'Status' => 'OK',
                'StatusCode' => 0,
                'TransactionStatus' => 'SUCCEEDED',
                'TransactionReference' => 'yo-txn-ref-pull',
            ]), 200, ['Content-Type' => 'text/xml']),
        ]);

        app(PaymentGatewayManager::class)
            ->driver(PaymentProvider::YoPayments)
            ->checkStatus('yo-txn-ref-pull', GatewayProvider::where('provider', 'yo_payments')->firstOrFail());

        Http::assertSent(function ($request) {
            return str_contains($request->body(), '<Method>actransactioncheckstatus</Method>')
                && str_contains($request->body(), '<DepositTransactionType>PULL</DepositTransactionType>')
                && ! str_contains($request->body(), '<DepositTransactionType>PUSH</DepositTransactionType>');
        });
    }

    public function test_yo_payments_failure_callback_does_not_credit_the_wallet_when_status_check_says_failed(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $this->gatewayProvider();
        $paymentLink = PaymentLink::factory()->create(['business_id' => $business->id, 'amount' => 5000]);

        $transaction = PaymentTransaction::create([
            'business_id' => $business->id,
            'payment_link_id' => $paymentLink->id,
            'provider' => 'yo_payments',
            'amount' => 5000,
            'currency' => 'UGX',
            'status' => 'processing',
            'external_reference' => 'txn-ref-yo-2',
            'provider_reference' => 'yo-txn-ref-999',
        ]);

        Http::fake([
            '*' => Http::response($this->xmlResponse([
                'Status' => 'OK',
                'StatusCode' => 0,
                'TransactionStatus' => 'FAILED',
                'TransactionReference' => 'yo-txn-ref-999',
            ]), 200, ['Content-Type' => 'text/xml']),
        ]);

        $this->post('/webhooks/yo-payments/failure', [
            'failed_transaction_reference' => 'txn-ref-yo-2',
        ])->assertOk();

        $this->assertSame('failed', $transaction->fresh()->status->value);
        $this->assertSame('0.00', (string) $business->wallet->fresh()->available_balance);
    }
}
