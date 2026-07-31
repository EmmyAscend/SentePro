<?php

namespace App\Policies;

use App\Models\User;

class WalletPolicy
{
    /**
     * Cross-tenant wallet monitoring is super-admin-only, granted entirely via
     * the Gate::before bypass in AppServiceProvider — no business-tier role
     * holds this ability. Business admins/staff see their own wallet balance
     * through the dashboard, not through this policy.
     */
    public function manage(User $user): bool
    {
        return false;
    }
}
