<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebhookAndAnalyticsWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_business_admin_can_store_a_webhook_event_for_a_transaction(): void
    {
        $business = Business::factory()->create([
            'business_name' => 'Webhook Business',
            'status' => 'approved',
        ]);

        $user = User::factory()->create([
            'role' => 'business_admin',
            'business_id' => $business->id,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->post('/webhooks', [
            'business_id' => $business->id,
            'provider' => 'flutterwave',
            'event' => 'payment.completed',
            'payload' => json_encode(['status' => 'completed', 'amount' => 7500]),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('webhook_events', [
            'business_id' => $business->id,
            'provider' => 'flutterwave',
            'event' => 'payment.completed',
        ]);
    }

    public function test_provider_webhook_updates_matching_payment_transaction_status(): void
    {
        $business = Business::factory()->create([
            'business_name' => 'Webhook Business',
            'status' => 'approved',
        ]);

        $user = User::factory()->create([
            'role' => 'business_admin',
            'business_id' => $business->id,
            'email_verified_at' => now(),
        ]);

        $transaction = PaymentTransaction::factory()->create([
            'business_id' => $business->id,
            'provider' => 'pesapal',
            'amount' => 7500,
            'currency' => 'UGX',
            'status' => 'processing',
            'external_reference' => 'txn-1001',
        ]);

        $response = $this->actingAs($user)->post('/webhooks', [
            'business_id' => $business->id,
            'provider' => 'pesapal',
            'event' => 'payment.completed',
            'payload' => json_encode([
                'status' => 'completed',
                'external_reference' => 'txn-1001',
                'amount' => 7500,
            ]),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('payment_transactions', [
            'id' => $transaction->id,
            'status' => 'completed',
            'external_reference' => 'txn-1001',
        ]);
    }

    public function test_authenticatd_business_admin_can_view_transaction_analytics_cards(): void
    {
        $business = Business::factory()->create([
            'business_name' => 'Analytics Business',
            'status' => 'approved',
        ]);

        $user = User::factory()->create([
            'role' => 'business_admin',
            'business_id' => $business->id,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/analytics');

        $response->assertOk();
        $response->assertSee('Analytics');
    }
}
