<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportTicketWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_admin_can_open_a_ticket_with_an_initial_message(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $admin = User::factory()->businessAdmin($business)->create();

        $response = $this->actingAs($admin)->post('/support-tickets', [
            'subject' => 'Cannot see my settlement',
            'body' => 'My settlement request from yesterday has not shown up.',
        ]);

        $ticket = SupportTicket::first();
        $response->assertRedirect(route('support-tickets.show', $ticket));

        $this->assertSame('Cannot see my settlement', $ticket->subject);
        $this->assertSame('open', $ticket->status->value);
        $this->assertDatabaseHas('support_ticket_messages', [
            'support_ticket_id' => $ticket->id,
            'user_id' => $admin->id,
            'body' => 'My settlement request from yesterday has not shown up.',
        ]);
    }

    public function test_staff_can_reply_to_their_own_businesss_ticket(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $admin = User::factory()->businessAdmin($business)->create();
        $staff = User::factory()->staff($business)->create();

        $ticket = SupportTicket::create([
            'business_id' => $business->id,
            'created_by' => $admin->id,
            'subject' => 'Question about fees',
            'status' => 'open',
        ]);

        $response = $this->actingAs($staff)->post("/support-tickets/{$ticket->id}/messages", [
            'body' => 'Following up on this.',
        ]);

        $response->assertRedirect(route('support-tickets.show', $ticket));
        $this->assertDatabaseHas('support_ticket_messages', [
            'support_ticket_id' => $ticket->id,
            'user_id' => $staff->id,
            'body' => 'Following up on this.',
        ]);
    }

    public function test_a_business_cannot_view_or_reply_to_another_businesss_ticket(): void
    {
        $businessA = Business::factory()->create(['status' => 'approved']);
        $businessB = Business::factory()->create(['status' => 'approved']);
        $adminA = User::factory()->businessAdmin($businessA)->create();
        $adminB = User::factory()->businessAdmin($businessB)->create();

        $ticket = SupportTicket::create([
            'business_id' => $businessA->id,
            'created_by' => $adminA->id,
            'subject' => 'Private matter',
            'status' => 'open',
        ]);

        $this->actingAs($adminB)->get("/support-tickets/{$ticket->id}")->assertNotFound();
        $this->actingAs($adminB)->post("/support-tickets/{$ticket->id}/messages", ['body' => 'Snooping'])->assertNotFound();
    }

    public function test_super_admin_sees_tickets_across_every_business_and_can_reply(): void
    {
        $businessA = Business::factory()->create(['status' => 'approved', 'business_name' => 'Business A']);
        $businessB = Business::factory()->create(['status' => 'approved', 'business_name' => 'Business B']);

        $ticketA = SupportTicket::create(['business_id' => $businessA->id, 'subject' => 'Issue A', 'status' => 'open']);
        $ticketB = SupportTicket::create(['business_id' => $businessB->id, 'subject' => 'Issue B', 'status' => 'open']);

        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->get('/support-tickets');
        $response->assertOk();
        $response->assertSee('Issue A');
        $response->assertSee('Issue B');

        $reply = $this->actingAs($superAdmin)->post("/support-tickets/{$ticketA->id}/messages", [
            'body' => 'Support is looking into this.',
        ]);
        $reply->assertRedirect(route('support-tickets.show', $ticketA));

        $this->assertDatabaseHas('support_ticket_messages', [
            'support_ticket_id' => $ticketA->id,
            'user_id' => $superAdmin->id,
        ]);
    }

    public function test_the_owning_business_can_resolve_and_reopen_its_own_ticket(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $admin = User::factory()->businessAdmin($business)->create();

        $ticket = SupportTicket::create([
            'business_id' => $business->id,
            'created_by' => $admin->id,
            'subject' => 'Resolved by me',
            'status' => 'open',
        ]);

        $this->actingAs($admin)->post("/support-tickets/{$ticket->id}/resolve")->assertRedirect();
        $this->assertSame('resolved', $ticket->fresh()->status->value);

        $this->actingAs($admin)->post("/support-tickets/{$ticket->id}/reopen")->assertRedirect();
        $this->assertSame('open', $ticket->fresh()->status->value);
    }

    public function test_a_business_reply_reopens_a_resolved_ticket(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $admin = User::factory()->businessAdmin($business)->create();

        $ticket = SupportTicket::create([
            'business_id' => $business->id,
            'created_by' => $admin->id,
            'subject' => 'Reopen me',
            'status' => 'resolved',
        ]);

        $this->actingAs($admin)->post("/support-tickets/{$ticket->id}/messages", [
            'body' => 'Actually still broken.',
        ])->assertRedirect();

        $this->assertSame('open', $ticket->fresh()->status->value);
    }

    public function test_a_support_reply_does_not_reopen_a_resolved_ticket(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $admin = User::factory()->businessAdmin($business)->create();
        $superAdmin = User::factory()->superAdmin()->create();

        $ticket = SupportTicket::create([
            'business_id' => $business->id,
            'created_by' => $admin->id,
            'subject' => 'Stay resolved',
            'status' => 'resolved',
        ]);

        $this->actingAs($superAdmin)->post("/support-tickets/{$ticket->id}/messages", [
            'body' => 'Closing this out, glad it works now.',
        ])->assertRedirect();

        $this->assertSame('resolved', $ticket->fresh()->status->value);
    }
}
