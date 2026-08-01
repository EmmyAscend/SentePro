<?php

namespace Tests\Feature;

use App\Mail\DisputeMail;
use App\Models\Business;
use App\Models\Dispute;
use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class DisputeWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function completedTransaction(Business $business): PaymentTransaction
    {
        return PaymentTransaction::factory()->create([
            'business_id' => $business->id,
            'provider' => 'pesapal',
            'amount' => 2500,
            'currency' => 'UGX',
            'status' => 'completed',
            'external_reference' => 'txn-dispute-1',
        ]);
    }

    public function test_business_admin_can_open_a_dispute_against_a_completed_transaction(): void
    {
        Mail::fake();

        $business = Business::factory()->create(['status' => 'approved']);
        $transaction = $this->completedTransaction($business);
        $admin = User::factory()->businessAdmin($business)->create();
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($admin)->post("/transactions/{$transaction->id}/disputes", [
            'reason' => 'Customer says the goods never arrived',
            'description' => 'Requesting investigation before we consider a refund.',
        ]);

        $dispute = Dispute::first();
        $response->assertRedirect(route('disputes.show', $dispute));

        $this->assertSame('open', $dispute->status->value);
        $this->assertSame($transaction->id, $dispute->payment_transaction_id);
        $this->assertSame($business->id, $dispute->business_id);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'dispute.opened',
            'subject_id' => $dispute->id,
        ]);

        Mail::assertQueued(DisputeMail::class, fn ($mail) => $mail->event === 'opened'
            && $mail->dispute->is($dispute)
            && $mail->hasTo($superAdmin->email));
    }

    public function test_opening_a_second_dispute_while_one_is_open_is_rejected(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $transaction = $this->completedTransaction($business);
        $admin = User::factory()->businessAdmin($business)->create();

        $this->actingAs($admin)->post("/transactions/{$transaction->id}/disputes", [
            'reason' => 'First dispute',
        ])->assertRedirect();

        $response = $this->actingAs($admin)->post("/transactions/{$transaction->id}/disputes", [
            'reason' => 'Second dispute',
        ]);

        $response->assertSessionHasErrors('transaction');
        $this->assertSame(1, Dispute::count());
    }

    public function test_a_processing_transaction_cannot_be_disputed(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $transaction = PaymentTransaction::factory()->create([
            'business_id' => $business->id,
            'provider' => 'pesapal',
            'status' => 'processing',
            'external_reference' => 'txn-dispute-pending-1',
        ]);
        $admin = User::factory()->businessAdmin($business)->create();

        $response = $this->actingAs($admin)->post("/transactions/{$transaction->id}/disputes", [
            'reason' => 'Too early to dispute',
        ]);

        $response->assertSessionHasErrors('transaction');
        $this->assertDatabaseMissing('disputes', ['payment_transaction_id' => $transaction->id]);
    }

    public function test_business_admin_cannot_resolve_or_reject_its_own_dispute(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $transaction = $this->completedTransaction($business);
        $admin = User::factory()->businessAdmin($business)->create();

        $this->actingAs($admin)->post("/transactions/{$transaction->id}/disputes", [
            'reason' => 'Not delivered',
        ])->assertRedirect();

        $dispute = Dispute::first();

        $this->actingAs($admin)->post("/admin/disputes/{$dispute->id}/resolve", ['notes' => 'Nice try'])->assertForbidden();
        $this->actingAs($admin)->post("/admin/disputes/{$dispute->id}/reject", ['notes' => 'Nice try'])->assertForbidden();
    }

    public function test_super_admin_can_resolve_a_dispute(): void
    {
        Mail::fake();

        $business = Business::factory()->create(['status' => 'approved']);
        $transaction = $this->completedTransaction($business);
        $admin = User::factory()->businessAdmin($business)->create();
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)->post("/transactions/{$transaction->id}/disputes", [
            'reason' => 'Not delivered',
        ])->assertRedirect();

        $dispute = Dispute::first();

        $response = $this->actingAs($superAdmin)->post("/admin/disputes/{$dispute->id}/resolve", [
            'notes' => 'Confirmed with courier — refund issued separately.',
        ]);

        $response->assertRedirect(route('disputes.show', $dispute));
        $dispute->refresh();
        $this->assertSame('resolved', $dispute->status->value);
        $this->assertSame($superAdmin->id, $dispute->resolved_by);
        $this->assertNotNull($dispute->resolved_at);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'dispute.resolved',
            'subject_id' => $dispute->id,
        ]);

        Mail::assertQueued(DisputeMail::class, fn ($mail) => $mail->event === 'resolved'
            && $mail->hasTo($admin->email));
    }

    public function test_super_admin_can_reject_a_dispute(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $transaction = $this->completedTransaction($business);
        $admin = User::factory()->businessAdmin($business)->create();
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)->post("/transactions/{$transaction->id}/disputes", [
            'reason' => 'Not delivered',
        ])->assertRedirect();

        $dispute = Dispute::first();

        $response = $this->actingAs($superAdmin)->post("/admin/disputes/{$dispute->id}/reject", [
            'notes' => 'Tracking confirms delivery.',
        ]);

        $response->assertRedirect(route('disputes.show', $dispute));
        $this->assertSame('rejected', $dispute->fresh()->status->value);
    }

    public function test_business_cannot_open_or_view_a_dispute_on_another_businesss_transaction(): void
    {
        $businessA = Business::factory()->create(['status' => 'approved']);
        $businessB = Business::factory()->create(['status' => 'approved']);
        $transaction = $this->completedTransaction($businessA);
        $adminB = User::factory()->businessAdmin($businessB)->create();

        // PaymentTransaction is tenant-scoped, so route-model-binding can't even
        // find another business's transaction for this user — 404, same
        // behavior as the refund route.
        $this->actingAs($adminB)->post("/transactions/{$transaction->id}/disputes", [
            'reason' => 'Snooping',
        ])->assertNotFound();

        $dispute = Dispute::create([
            'business_id' => $businessA->id,
            'payment_transaction_id' => $transaction->id,
            'raised_by' => User::factory()->businessAdmin($businessA)->create()->id,
            'reason' => 'Private dispute',
            'status' => 'open',
        ]);

        $this->actingAs($adminB)->get("/disputes/{$dispute->id}")->assertNotFound();
    }
}
