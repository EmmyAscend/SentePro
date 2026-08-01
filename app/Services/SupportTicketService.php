<?php

namespace App\Services;

use App\Enums\SupportTicketStatus;
use App\Models\Business;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SupportTicketService
{
    public function open(Business $business, User $user, string $subject, string $body): SupportTicket
    {
        return DB::transaction(function () use ($business, $user, $subject, $body) {
            $ticket = SupportTicket::create([
                'business_id' => $business->id,
                'created_by' => $user->id,
                'subject' => $subject,
                'status' => SupportTicketStatus::Open,
            ]);

            SupportTicketMessage::create([
                'business_id' => $business->id,
                'support_ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'body' => $body,
            ]);

            return $ticket;
        });
    }

    /**
     * A business replying to a resolved ticket implicitly reopens it — a
     * support reply never does, since that's the side closing it out.
     */
    public function reply(SupportTicket $ticket, User $user, string $body): SupportTicketMessage
    {
        return DB::transaction(function () use ($ticket, $user, $body) {
            $message = SupportTicketMessage::create([
                'business_id' => $ticket->business_id,
                'support_ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'body' => $body,
            ]);

            if ($ticket->status === SupportTicketStatus::Resolved && ! $user->isSuperAdmin()) {
                $ticket->update(['status' => SupportTicketStatus::Open]);
            }

            return $message;
        });
    }

    public function resolve(SupportTicket $ticket): SupportTicket
    {
        $ticket->update(['status' => SupportTicketStatus::Resolved]);

        return $ticket->fresh();
    }

    public function reopen(SupportTicket $ticket): SupportTicket
    {
        $ticket->update(['status' => SupportTicketStatus::Open]);

        return $ticket->fresh();
    }
}
