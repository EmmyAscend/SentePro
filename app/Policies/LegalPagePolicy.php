<?php

namespace App\Policies;

use App\Models\User;

class LegalPagePolicy
{
    /**
     * Editing legal pages is super-admin-only, granted entirely via the
     * Gate::before bypass in AppServiceProvider.
     */
    public function manage(User $user): bool
    {
        return false;
    }
}
