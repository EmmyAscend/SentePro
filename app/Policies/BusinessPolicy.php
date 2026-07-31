<?php

namespace App\Policies;

use App\Models\Business;
use App\Models\User;

class BusinessPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->business_id !== null;
    }

    public function view(User $user, Business $business): bool
    {
        return $user->business_id === $business->id;
    }

    /**
     * Approve/reject/suspend a business. Only super admins may review businesses
     * (granted via the Gate::before bypass) — no business-tier role holds this ability.
     */
    public function review(User $user, Business $business): bool
    {
        return false;
    }

    /**
     * Browsing the full cross-tenant businesses list is super-admin-only,
     * granted entirely via the Gate::before bypass in AppServiceProvider.
     * Distinct from viewAny()/view() above, which gate a business admin's
     * view of their own single business.
     */
    public function manage(User $user): bool
    {
        return false;
    }
}
