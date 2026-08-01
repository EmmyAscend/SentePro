<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletTransferTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_admin_can_send_a_transfer_by_business_id(): void
    {
        $sender = Business::factory()->create(['status' => 'approved']);
        $sender->wallet->update(['available_balance' => 5000]);
        $recipient = Business::factory()->create(['status' => 'approved']);
        // Cache the relation now, while unauthenticated — Wallet is tenant-scoped,
        // so lazy-loading it after actingAs() as the sender would filter it out.
        $recipient->load('wallet');
        $admin = User::factory()->businessAdmin($sender)->create();

        $response = $this->actingAs($admin)->post('/wallet-transfers', [
            'recipient' => (string) $recipient->id,
            'amount' => 1500,
            'note' => 'Supplier payment',
        ]);

        $response->assertRedirect(route('wallet-transfers.index'));
        $this->assertSame('3500.00', (string) $sender->wallet->fresh()->available_balance);
        $this->assertSame('1500.00', (string) $recipient->wallet->fresh()->available_balance);

        $this->assertDatabaseHas('wallet_transfers', [
            'sender_business_id' => $sender->id,
            'recipient_business_id' => $recipient->id,
            'amount' => 1500,
            'note' => 'Supplier payment',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'wallet.transferred',
        ]);
    }

    public function test_business_admin_can_send_a_transfer_by_email(): void
    {
        $sender = Business::factory()->create(['status' => 'approved']);
        $sender->wallet->update(['available_balance' => 5000]);
        $recipient = Business::factory()->create(['status' => 'approved', 'email' => 'unique-recipient@example.test']);
        $recipient->load('wallet');
        $admin = User::factory()->businessAdmin($sender)->create();

        $response = $this->actingAs($admin)->post('/wallet-transfers', [
            'recipient' => 'unique-recipient@example.test',
            'amount' => 1000,
        ]);

        $response->assertRedirect(route('wallet-transfers.index'));
        $this->assertSame('1000.00', (string) $recipient->wallet->fresh()->available_balance);
    }

    public function test_business_admin_can_send_a_transfer_by_phone(): void
    {
        $sender = Business::factory()->create(['status' => 'approved']);
        $sender->wallet->update(['available_balance' => 5000]);
        $recipient = Business::factory()->create(['status' => 'approved', 'phone' => '+256700999888']);
        $recipient->load('wallet');
        $admin = User::factory()->businessAdmin($sender)->create();

        $response = $this->actingAs($admin)->post('/wallet-transfers', [
            'recipient' => '+256700999888',
            'amount' => 1000,
        ]);

        $response->assertRedirect(route('wallet-transfers.index'));
        $this->assertSame('1000.00', (string) $recipient->wallet->fresh()->available_balance);
    }

    public function test_an_ambiguous_email_is_rejected_without_guessing(): void
    {
        $sender = Business::factory()->create(['status' => 'approved']);
        $sender->wallet->update(['available_balance' => 5000]);
        Business::factory()->create(['status' => 'approved', 'email' => 'shared@example.test']);
        Business::factory()->create(['status' => 'approved', 'email' => 'shared@example.test']);
        $admin = User::factory()->businessAdmin($sender)->create();

        $response = $this->actingAs($admin)->post('/wallet-transfers', [
            'recipient' => 'shared@example.test',
            'amount' => 1000,
        ]);

        $response->assertSessionHasErrors('recipient');
        $this->assertSame('5000.00', (string) $sender->wallet->fresh()->available_balance);
    }

    public function test_a_non_existent_recipient_is_rejected(): void
    {
        $sender = Business::factory()->create(['status' => 'approved']);
        $sender->wallet->update(['available_balance' => 5000]);
        $admin = User::factory()->businessAdmin($sender)->create();

        $response = $this->actingAs($admin)->post('/wallet-transfers', [
            'recipient' => 'nobody@example.test',
            'amount' => 1000,
        ]);

        $response->assertSessionHasErrors('recipient');
    }

    public function test_a_business_cannot_transfer_to_itself(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $business->wallet->update(['available_balance' => 5000]);
        $admin = User::factory()->businessAdmin($business)->create();

        $response = $this->actingAs($admin)->post('/wallet-transfers', [
            'recipient' => (string) $business->id,
            'amount' => 1000,
        ]);

        $response->assertSessionHasErrors('recipient');
        $this->assertSame('5000.00', (string) $business->wallet->fresh()->available_balance);
    }

    public function test_transfers_to_a_non_approved_business_are_rejected(): void
    {
        $sender = Business::factory()->create(['status' => 'approved']);
        $sender->wallet->update(['available_balance' => 5000]);
        $recipient = Business::factory()->create(['status' => 'pending']);
        $admin = User::factory()->businessAdmin($sender)->create();

        $response = $this->actingAs($admin)->post('/wallet-transfers', [
            'recipient' => (string) $recipient->id,
            'amount' => 1000,
        ]);

        $response->assertSessionHasErrors('recipient');
        $this->assertSame('5000.00', (string) $sender->wallet->fresh()->available_balance);
    }

    public function test_a_transfer_exceeding_the_available_balance_is_rejected(): void
    {
        $sender = Business::factory()->create(['status' => 'approved']);
        $sender->wallet->update(['available_balance' => 500]);
        $recipient = Business::factory()->create(['status' => 'approved']);
        $recipient->load('wallet');
        $admin = User::factory()->businessAdmin($sender)->create();

        $response = $this->actingAs($admin)->post('/wallet-transfers', [
            'recipient' => (string) $recipient->id,
            'amount' => 1000,
        ]);

        $response->assertSessionHasErrors('amount');
        $this->assertSame('500.00', (string) $sender->wallet->fresh()->available_balance);
        $this->assertSame('0.00', (string) $recipient->wallet->fresh()->available_balance);
    }

    public function test_staff_without_permission_cannot_send_a_transfer(): void
    {
        $sender = Business::factory()->create(['status' => 'approved']);
        $sender->wallet->update(['available_balance' => 5000]);
        $recipient = Business::factory()->create(['status' => 'approved']);
        $staff = User::factory()->staff($sender)->create();

        $response = $this->actingAs($staff)->post('/wallet-transfers', [
            'recipient' => (string) $recipient->id,
            'amount' => 1000,
        ]);

        $response->assertForbidden();
    }

    public function test_staff_with_permission_can_send_a_transfer(): void
    {
        $sender = Business::factory()->create(['status' => 'approved']);
        $sender->wallet->update(['available_balance' => 5000]);
        $recipient = Business::factory()->create(['status' => 'approved']);
        $recipient->load('wallet');
        $staff = User::factory()->staff($sender, ['wallet-transfers.create'])->create();

        $response = $this->actingAs($staff)->post('/wallet-transfers', [
            'recipient' => (string) $recipient->id,
            'amount' => 1000,
        ]);

        $response->assertRedirect(route('wallet-transfers.index'));
        $this->assertSame('1000.00', (string) $recipient->wallet->fresh()->available_balance);
    }

    public function test_the_receive_qr_code_endpoint_returns_a_scannable_svg_for_an_authenticated_user(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $admin = User::factory()->businessAdmin($business)->create();

        $response = $this->actingAs($admin)->get('/wallet-transfers/receive-qr-code');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/svg+xml');
        $this->assertStringContainsString('<svg', $response->getContent());
    }

    public function test_the_history_page_shows_both_sent_and_received_transfers(): void
    {
        $businessA = Business::factory()->create(['status' => 'approved', 'business_name' => 'Sender Co']);
        $businessA->wallet->update(['available_balance' => 5000]);
        $businessB = Business::factory()->create(['status' => 'approved', 'business_name' => 'Receiver Co']);
        $adminA = User::factory()->businessAdmin($businessA)->create();

        $this->actingAs($adminA)->post('/wallet-transfers', [
            'recipient' => (string) $businessB->id,
            'amount' => 750,
        ])->assertRedirect();

        $adminB = User::factory()->businessAdmin($businessB)->create();

        $responseA = $this->actingAs($adminA)->get('/wallet-transfers');
        $responseA->assertOk();
        $responseA->assertSee('Sent to Receiver Co');

        $responseB = $this->actingAs($adminB)->get('/wallet-transfers');
        $responseB->assertOk();
        $responseB->assertSee('Received from Sender Co');
    }
}
