<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\FeeBreakdown;
use App\Models\PaymentTransaction;
use App\Models\Receipt;
use App\Models\Settlement;
use App\Models\SettlementMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportingExportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_settlement_export_returns_csv_scoped_to_the_business(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $otherBusiness = Business::factory()->create(['status' => 'approved']);
        $method = SettlementMethod::factory()->create();

        Settlement::create([
            'business_id' => $business->id,
            'settlement_method_id' => $method->id,
            'amount' => 2500, 'gateway_fee' => 50, 'platform_fee' => 25, 'net_amount' => 2425,
            'status' => 'pending',
        ]);
        Settlement::create([
            'business_id' => $otherBusiness->id,
            'settlement_method_id' => $method->id,
            'amount' => 9999, 'gateway_fee' => 0, 'platform_fee' => 0, 'net_amount' => 9999,
            'status' => 'pending',
        ]);

        $admin = User::factory()->businessAdmin($business)->create();

        $response = $this->actingAs($admin)->get('/settlements/export');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();
        $this->assertStringContainsString('Business,Method,Amount', $content);
        $this->assertStringContainsString('2500', $content);
        $this->assertStringNotContainsString('9999', $content);
    }

    public function test_receipt_export_route_is_not_swallowed_by_the_receipt_show_route(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $transaction = PaymentTransaction::factory()->create(['business_id' => $business->id]);
        $receipt = Receipt::create([
            'business_id' => $business->id,
            'payment_transaction_id' => $transaction->id,
            'reference_number' => 'RCPT-EXPORTTEST',
            'amount' => 1000, 'net_amount' => 950, 'currency' => 'UGX',
        ]);
        $admin = User::factory()->businessAdmin($business)->create();

        $exportResponse = $this->actingAs($admin)->get('/receipts/export');
        $exportResponse->assertOk();
        $exportResponse->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('RCPT-EXPORTTEST', $exportResponse->streamedContent());

        // the public show route for a real receipt must still work unaffected
        $this->get('/receipts/RCPT-EXPORTTEST')->assertOk();
    }

    public function test_receipt_export_is_scoped_to_the_business(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $otherBusiness = Business::factory()->create(['status' => 'approved']);
        $transaction = PaymentTransaction::factory()->create(['business_id' => $business->id]);
        $otherTransaction = PaymentTransaction::factory()->create(['business_id' => $otherBusiness->id]);

        Receipt::create([
            'business_id' => $business->id, 'payment_transaction_id' => $transaction->id,
            'reference_number' => 'RCPT-OWNBIZ', 'amount' => 1000, 'net_amount' => 950, 'currency' => 'UGX',
        ]);
        Receipt::create([
            'business_id' => $otherBusiness->id, 'payment_transaction_id' => $otherTransaction->id,
            'reference_number' => 'RCPT-OTHERBIZ', 'amount' => 2000, 'net_amount' => 1900, 'currency' => 'UGX',
        ]);

        $admin = User::factory()->businessAdmin($business)->create();

        $content = $this->actingAs($admin)->get('/receipts/export')->streamedContent();

        $this->assertStringContainsString('RCPT-OWNBIZ', $content);
        $this->assertStringNotContainsString('RCPT-OTHERBIZ', $content);
    }

    public function test_fee_breakdown_export_returns_csv_scoped_to_the_business(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $otherBusiness = Business::factory()->create(['status' => 'approved']);

        FeeBreakdown::create([
            'business_id' => $business->id, 'transaction_reference' => 'txn-own-biz',
            'gateway_fee' => 10, 'platform_fee' => 5, 'net_amount' => 985,
        ]);
        FeeBreakdown::create([
            'business_id' => $otherBusiness->id, 'transaction_reference' => 'txn-other-biz',
            'gateway_fee' => 10, 'platform_fee' => 5, 'net_amount' => 985,
        ]);

        $admin = User::factory()->businessAdmin($business)->create();

        $content = $this->actingAs($admin)->get('/fee-breakdowns/export')->streamedContent();

        $this->assertStringContainsString('txn-own-biz', $content);
        $this->assertStringNotContainsString('txn-other-biz', $content);
    }

    public function test_super_admin_export_includes_every_business(): void
    {
        $businessA = Business::factory()->create(['status' => 'approved']);
        $businessB = Business::factory()->create(['status' => 'approved']);

        FeeBreakdown::create([
            'business_id' => $businessA->id, 'transaction_reference' => 'txn-business-a',
            'gateway_fee' => 10, 'platform_fee' => 5, 'net_amount' => 985,
        ]);
        FeeBreakdown::create([
            'business_id' => $businessB->id, 'transaction_reference' => 'txn-business-b',
            'gateway_fee' => 10, 'platform_fee' => 5, 'net_amount' => 985,
        ]);

        $superAdmin = User::factory()->superAdmin()->create();

        $content = $this->actingAs($superAdmin)->get('/fee-breakdowns/export')->streamedContent();

        $this->assertStringContainsString('txn-business-a', $content);
        $this->assertStringContainsString('txn-business-b', $content);
    }
}
