<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * List/manage staff. Only business admins manage staff for their own business
     * (super admins already bypass via Gate::before); staff never manage other staff.
     */
    public function viewAny(User $user): bool
    {
        return $user->isBusinessAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isBusinessAdmin();
    }
}
