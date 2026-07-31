<?php

namespace App\Policies;

use App\Models\User;

class FeeStructurePolicy
{
    /**
     * Configuring transaction fees is super-admin-only, granted entirely via
     * the Gate::before bypass in AppServiceProvider — no business-tier role
     * holds this ability.
     */
    public function manage(User $user): bool
    {
        return false;
    }
}
