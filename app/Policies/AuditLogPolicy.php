<?php

namespace App\Policies;

use App\Models\User;

class AuditLogPolicy
{
    /**
     * Browsing the audit trail is super-admin-only, granted entirely via
     * the Gate::before bypass in AppServiceProvider — no business-tier role
     * holds this ability.
     */
    public function manage(User $user): bool
    {
        return false;
    }
}
