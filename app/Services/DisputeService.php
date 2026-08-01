<?php

namespace App\Services;

use App\Enums\DisputeStatus;
use App\Enums\PaymentTransactionStatus;
use App\Enums\UserRole;
use App\Mail\DisputeMail;
use App\Models\Dispute;
use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class DisputeService
{
    public function __construct(private readonly AuditLogService $auditLog) {}

    public function open(PaymentTransaction $transaction, User $user, string $reason, ?string $description): Dispute
    {
        if (! in_array($transaction->status, [
            PaymentTransactionStatus::Completed,
            PaymentTransactionStatus::PartiallyRefunded,
            PaymentTransactionStatus::Refunded,
        ], true)) {
            throw ValidationException::withMessages([
                'transaction' => 'Only completed transactions can be disputed.',
            ]);
        }

        $hasOpenDispute = Dispute::query()
            ->where('payment_transaction_id', $transaction->id)
            ->where('status', DisputeStatus::Open)
            ->exists();

        if ($hasOpenDispute) {
            throw ValidationException::withMessages([
                'transaction' => 'This transaction already has an open dispute.',
            ]);
        }

        $dispute = Dispute::create([
            'business_id' => $transaction->business_id,
            'payment_transaction_id' => $transaction->id,
            'raised_by' => $user->id,
            'reason' => $reason,
            'description' => $description,
            'status' => DisputeStatus::Open,
        ]);

        $this->auditLog->record('dispute.opened', $dispute, ['reason' => $reason]);

        $recipients = User::query()->where('role', UserRole::SuperAdmin)->pluck('email');

        if ($recipients->isNotEmpty()) {
            Mail::to($recipients)->queue(new DisputeMail($dispute, 'opened'));
        }

        return $dispute;
    }

    public function resolve(Dispute $dispute, User $resolver, string $notes): Dispute
    {
        return $this->finalize($dispute, $resolver, $notes, DisputeStatus::Resolved, 'dispute.resolved', 'resolved');
    }

    public function reject(Dispute $dispute, User $resolver, string $notes): Dispute
    {
        return $this->finalize($dispute, $resolver, $notes, DisputeStatus::Rejected, 'dispute.rejected', 'rejected');
    }

    private function finalize(Dispute $dispute, User $resolver, string $notes, DisputeStatus $status, string $auditAction, string $mailEvent): Dispute
    {
        if ($dispute->status !== DisputeStatus::Open) {
            throw ValidationException::withMessages([
                'status' => 'Only open disputes can be resolved or rejected.',
            ]);
        }

        $dispute->update([
            'status' => $status,
            'resolution_notes' => $notes,
            'resolved_by' => $resolver->id,
            'resolved_at' => now(),
        ]);

        $this->auditLog->record($auditAction, $dispute, ['notes' => $notes]);

        $recipients = $dispute->business->admins->pluck('email');

        if ($recipients->isNotEmpty()) {
            Mail::to($recipients)->queue(new DisputeMail($dispute, $mailEvent));
        }

        return $dispute->fresh();
    }
}
