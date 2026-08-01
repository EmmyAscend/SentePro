<?php

namespace App\Services;

use App\Mail\BusinessReviewedMail;
use App\Models\Business;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class BusinessVerificationService
{
    public function __construct(private readonly AuditLogService $auditLog) {}

    public function review(Business $business, User $reviewer, string $status, ?string $notes): Business
    {
        $business->update([
            'status' => $status,
            'review_notes' => $notes,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);

        $this->auditLog->record('business.reviewed', $business, [
            'status' => $status,
            'notes' => $notes,
        ]);

        $business = $business->fresh();

        if (in_array($status, ['approved', 'rejected', 'suspended'], true)) {
            $recipients = $business->admins->pluck('email');

            if ($recipients->isNotEmpty()) {
                Mail::to($recipients)->queue(new BusinessReviewedMail($business, $status));
            }
        }

        return $business;
    }
}
