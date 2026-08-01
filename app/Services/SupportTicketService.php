<?php

namespace App\Services;

use App\Enums\SupportTicketStatus;
use App\Enums\UserRole;
use App\Mail\SupportTicketMail;
use App\Models\Business;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SupportTicketService
{
    public function open(Business $business, User $user, string $subject, string $body): SupportTicket
    {
        [$ticket, $message] = DB::transaction(function () use ($business, $user, $subject, $body) {
            $ticket = SupportTicket::create([
                'business_id' => $business->id,
                'created_by' => $user->id,
                'subject' => $subject,
                'status' => SupportTicketStatus::Open,
            ]);

            $message = SupportTicketMessage::create([
                'business_id' => $business->id,
                'support_ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'body' => $body,
            ]);

            return [$ticket, $message];
        });

        $this->notify($this->superAdminEmails(), $ticket, $message, 'opened');

        return $ticket;
    }

    /**
     * A business replying to a resolved ticket implicitly reopens it — a
     * support reply never does, since that's the side closing it out.
     */
    public function reply(SupportTicket $ticket, User $user, string $body): SupportTicketMessage
    {
        $message = DB::transaction(function () use ($ticket, $user, $body) {
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

        $recipients = $user->isSuperAdmin()
            ? $ticket->business->admins->pluck('email')
            : $this->superAdminEmails();

        $this->notify($recipients, $ticket, $message, 'replied');

        return $message;
    }

    private function superAdminEmails(): Collection
    {
        return User::query()->where('role', UserRole::SuperAdmin)->pluck('email');
    }

    private function notify(Collection $recipients, SupportTicket $ticket, SupportTicketMessage $message, string $event): void
    {
        if ($recipients->isNotEmpty()) {
            Mail::to($recipients)->queue(new SupportTicketMail($ticket, $message, $event));
        }
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
