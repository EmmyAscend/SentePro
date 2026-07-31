<?php

namespace App\Services;

use App\Models\Business;
use App\Models\User;

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

        return $business->fresh();
    }
}
