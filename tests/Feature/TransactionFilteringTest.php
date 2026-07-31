<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionFilteringTest extends TestCase
{
    use RefreshDatabase;

    public function test_filtering_by_status_narrows_the_ledger(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        PaymentTransaction::factory()->create(['business_id' => $business->id, 'status' => 'completed', 'external_reference' => 'txn-completed-1']);
        PaymentTransaction::factory()->create(['business_id' => $business->id, 'status' => 'failed', 'external_reference' => 'txn-failed-1']);
        $admin = User::factory()->businessAdmin($business)->create();

        $response = $this->actingAs($admin)->get('/transactions?status=failed');

        $response->assertOk();
        $response->assertSee('txn-failed-1');
        $response->assertDontSee('txn-completed-1');
    }

    public function test_filtering_by_provider_narrows_the_ledger(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        PaymentTransaction::factory()->create(['business_id' => $business->id, 'provider' => 'pesapal', 'external_reference' => 'txn-pesapal-1']);
        PaymentTransaction::factory()->create(['business_id' => $business->id, 'provider' => 'yo_payments', 'external_reference' => 'txn-yo-1']);
        $admin = User::factory()->businessAdmin($business)->create();

        $response = $this->actingAs($admin)->get('/transactions?provider=yo_payments');

        $response->assertOk();
        $response->assertSee('txn-yo-1');
        $response->assertDontSee('txn-pesapal-1');
    }

    public function test_search_matches_customer_details_and_reference(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        PaymentTransaction::factory()->create([
            'business_id' => $business->id,
            'external_reference' => 'txn-search-1',
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.test',
        ]);
        PaymentTransaction::factory()->create([
            'business_id' => $business->id,
            'external_reference' => 'txn-search-2',
            'customer_name' => 'John Smith',
            'customer_email' => 'john@example.test',
        ]);
        $admin = User::factory()->businessAdmin($business)->create();

        $response = $this->actingAs($admin)->get('/transactions?search=jane@example.test');

        $response->assertOk();
        $response->assertSee('txn-search-1');
        $response->assertDontSee('txn-search-2');
    }

    public function test_date_range_narrows_the_ledger(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $inRange = PaymentTransaction::factory()->create(['business_id' => $business->id, 'external_reference' => 'txn-in-range']);
        $inRange->forceFill(['created_at' => now()->subDays(5)])->save();

        $outOfRange = PaymentTransaction::factory()->create(['business_id' => $business->id, 'external_reference' => 'txn-out-of-range']);
        $outOfRange->forceFill(['created_at' => now()->subDays(30)])->save();

        $admin = User::factory()->businessAdmin($business)->create();

        $response = $this->actingAs($admin)->get('/transactions?date_from='.now()->subDays(10)->toDateString());

        $response->assertOk();
        $response->assertSee('txn-in-range');
        $response->assertDontSee('txn-out-of-range');
    }

    public function test_the_ledger_paginates_at_25_per_page(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        PaymentTransaction::factory()->count(30)->create(['business_id' => $business->id]);
        $admin = User::factory()->businessAdmin($business)->create();

        $page1 = $this->actingAs($admin)->get('/transactions');
        $page2 = $this->actingAs($admin)->get('/transactions?page=2');

        $page1->assertOk();
        $page1->assertViewHas('transactions', fn ($transactions) => $transactions->count() === 25);

        $page2->assertOk();
        $page2->assertViewHas('transactions', fn ($transactions) => $transactions->count() === 5);
    }

    public function test_super_admin_can_filter_the_ledger_by_business(): void
    {
        $businessA = Business::factory()->create(['status' => 'approved']);
        $businessB = Business::factory()->create(['status' => 'approved']);
        PaymentTransaction::factory()->create(['business_id' => $businessA->id, 'external_reference' => 'txn-business-a']);
        PaymentTransaction::factory()->create(['business_id' => $businessB->id, 'external_reference' => 'txn-business-b']);
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->get('/transactions?business_id='.$businessA->id);

        $response->assertOk();
        $response->assertSee('txn-business-a');
        $response->assertDontSee('txn-business-b');
    }

    public function test_csv_export_respects_active_filters_and_is_not_limited_by_pagination(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        PaymentTransaction::factory()->count(30)->create(['business_id' => $business->id, 'status' => 'completed']);
        PaymentTransaction::factory()->create(['business_id' => $business->id, 'status' => 'failed', 'external_reference' => 'txn-should-be-excluded']);
        $admin = User::factory()->businessAdmin($business)->create();

        $response = $this->actingAs($admin)->get('/transactions/export?status=completed');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();
        $rows = array_filter(explode("\n", trim($content)));

        // header row + all 30 completed transactions, not capped at the 25-per-page UI limit
        $this->assertCount(31, $rows);
        $this->assertStringContainsString('Business,Provider,Amount', $rows[0]);
        $this->assertStringNotContainsString('txn-should-be-excluded', $content);
    }

    public function test_a_business_admins_export_cannot_be_forged_to_include_another_businesss_transactions(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $otherBusiness = Business::factory()->create(['status' => 'approved']);
        PaymentTransaction::factory()->create(['business_id' => $business->id, 'external_reference' => 'txn-own-business']);
        PaymentTransaction::factory()->create(['business_id' => $otherBusiness->id, 'external_reference' => 'txn-other-business']);
        $admin = User::factory()->businessAdmin($business)->create();

        $response = $this->actingAs($admin)->get('/transactions/export?business_id='.$otherBusiness->id);

        $content = $response->streamedContent();

        $this->assertStringContainsString('txn-own-business', $content);
        $this->assertStringNotContainsString('txn-other-business', $content);
    }
}
