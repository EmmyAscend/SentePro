<?php

namespace Tests\Feature;

use App\Mail\RefundProcessedMail;
use App\Models\Business;
use App\Models\GatewayProvider;
use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RefundWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function gatewayProvider(Business $business, string $provider = 'pesapal'): GatewayProvider
    {
        return GatewayProvider::create([
            'business_id' => $business->id,
            'name' => 'Test Gateway',
            'provider' => $provider,
            'status' => 'active',
            'environment' => 'sandbox',
            'webhook_url' => 'https://example.test/webhooks/'.$provider.'/1',
            'credentials' => $provider === 'pesapal'
                ? ['consumer_key' => 'test-key', 'consumer_secret' => 'test-secret']
                : ['api_username' => 'test-user', 'api_password' => 'test-pass'],
            'supported_countries' => 'UG',
            'supported_currencies' => 'UGX',
        ]);
    }

    private function completedTransaction(Business $business): PaymentTransaction
    {
        return PaymentTransaction::factory()->create([
            'business_id' => $business->id,
            'provider' => 'pesapal',
            'amount' => 2500,
            'currency' => 'UGX',
            'status' => 'completed',
            'external_reference' => 'txn-refund-1',
            'provider_reference' => 'tracking-refund-1',
            'customer_email' => 'refund-customer@example.test',
        ]);
    }

    private function fakeSuccessfulRefund(): void
    {
        Http::fake([
            '*/api/Auth/RequestToken' => Http::response(['token' => 'fake-token', 'status' => '200']),
            '*/api/Transactions/GetTransactionStatus*' => Http::response([
                'payment_status_code' => 1,
                'confirmation_code' => 'CONF-REFUND-1',
                'status' => '200',
            ]),
            '*/api/Transactions/RefundRequest' => Http::response(['status' => '200', 'message' => 'Refund accepted']),
        ]);
    }

    public function test_business_admin_can_refund_a_completed_pesapal_transaction(): void
    {
        Mail::fake();

        $business = Business::factory()->create(['status' => 'approved']);
        $this->gatewayProvider($business);
        $transaction = $this->completedTransaction($business);
        $admin = User::factory()->businessAdmin($business)->create();

        $this->fakeSuccessfulRefund();

        $response = $this->actingAs($admin)->post("/transactions/{$transaction->id}/refund", [
            'reason' => 'Customer requested cancellation',
        ]);

        $response->assertRedirect(route('transactions.index'));

        $this->assertSame('refunded', $transaction->fresh()->status->value);

        $wallet = $business->wallet->fresh();
        $this->assertSame('0.00', (string) $wallet->available_balance);
        $this->assertSame('0.00', (string) $wallet->settlement_balance);

        $this->assertDatabaseHas('refunds', [
            'payment_transaction_id' => $transaction->id,
            'business_id' => $business->id,
            'status' => 'completed',
            'net_amount_reversed' => '2500.00',
            'reason' => 'Customer requested cancellation',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'transaction.refunded',
            'subject_id' => $transaction->id,
        ]);

        Mail::assertQueued(RefundProcessedMail::class, fn ($mail) => $mail->refund->payment_transaction_id === $transaction->id
            && $mail->hasTo('refund-customer@example.test'));
    }

    public function test_a_declined_refund_leaves_the_wallet_and_transaction_unchanged_but_records_the_attempt(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $this->gatewayProvider($business);
        $transaction = $this->completedTransaction($business);
        $admin = User::factory()->businessAdmin($business)->create();

        Http::fake([
            '*/api/Auth/RequestToken' => Http::response(['token' => 'fake-token', 'status' => '200']),
            '*/api/Transactions/GetTransactionStatus*' => Http::response([
                'payment_status_code' => 1,
                'confirmation_code' => 'CONF-REFUND-2',
                'status' => '200',
            ]),
            '*/api/Transactions/RefundRequest' => Http::response(['status' => '500', 'message' => 'Refund declined']),
        ]);

        $response = $this->actingAs($admin)->post("/transactions/{$transaction->id}/refund");

        $response->assertSessionHasErrors('transaction');
        $this->assertSame('completed', $transaction->fresh()->status->value);
        $this->assertSame('2500.00', (string) $business->wallet->fresh()->available_balance);

        $this->assertDatabaseHas('refunds', [
            'payment_transaction_id' => $transaction->id,
            'status' => 'failed',
            'net_amount_reversed' => '0.00',
        ]);
    }

    public function test_a_yo_payments_transaction_cannot_be_refunded(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $this->gatewayProvider($business, 'yo_payments');
        $transaction = PaymentTransaction::factory()->create([
            'business_id' => $business->id,
            'provider' => 'yo_payments',
            'amount' => 2500,
            'currency' => 'UGX',
            'status' => 'completed',
            'external_reference' => 'txn-refund-yo-1',
        ]);
        $admin = User::factory()->businessAdmin($business)->create();

        $response = $this->actingAs($admin)->post("/transactions/{$transaction->id}/refund");

        $response->assertSessionHasErrors('transaction');
        $this->assertSame('completed', $transaction->fresh()->status->value);
        $this->assertDatabaseMissing('refunds', ['payment_transaction_id' => $transaction->id]);
    }

    public function test_a_non_completed_transaction_cannot_be_refunded(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $this->gatewayProvider($business);
        $transaction = PaymentTransaction::factory()->create([
            'business_id' => $business->id,
            'provider' => 'pesapal',
            'status' => 'processing',
            'external_reference' => 'txn-refund-pending-1',
        ]);
        $admin = User::factory()->businessAdmin($business)->create();

        $response = $this->actingAs($admin)->post("/transactions/{$transaction->id}/refund");

        $response->assertSessionHasErrors('transaction');
        $this->assertDatabaseMissing('refunds', ['payment_transaction_id' => $transaction->id]);
    }

    public function test_staff_without_permission_cannot_refund(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $this->gatewayProvider($business);
        $transaction = $this->completedTransaction($business);
        $staff = User::factory()->staff($business)->create();

        $response = $this->actingAs($staff)->post("/transactions/{$transaction->id}/refund");

        $response->assertForbidden();
        $this->assertSame('completed', $transaction->fresh()->status->value);
    }

    public function test_staff_with_permission_can_refund(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $this->gatewayProvider($business);
        $transaction = $this->completedTransaction($business);
        $staff = User::factory()->staff($business, ['transactions.refund'])->create();

        $this->fakeSuccessfulRefund();

        $response = $this->actingAs($staff)->post("/transactions/{$transaction->id}/refund");

        $response->assertRedirect(route('transactions.index'));
        $this->assertSame('refunded', $transaction->fresh()->status->value);
    }

    public function test_business_admin_cannot_refund_another_businesss_transaction(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $otherBusiness = Business::factory()->create(['status' => 'approved']);
        $this->gatewayProvider($otherBusiness);
        $transaction = $this->completedTransaction($otherBusiness);
        $admin = User::factory()->businessAdmin($business)->create();

        // PaymentTransaction is tenant-scoped, so route-model-binding can't even
        // find another business's transaction for this user — 404, not 403,
        // same behavior as every other tenant-scoped model in this app.
        $response = $this->actingAs($admin)->post("/transactions/{$transaction->id}/refund");

        $response->assertNotFound();
        $this->assertSame('completed', $transaction->fresh()->status->value);
    }
}
