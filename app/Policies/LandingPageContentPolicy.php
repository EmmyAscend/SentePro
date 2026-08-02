<?php

namespace App\Policies;

use App\Models\User;

class LandingPageContentPolicy
{
    /**
     * Editing the public landing page is super-admin-only, granted entirely
     * via the Gate::before bypass in AppServiceProvider.
     */
    public function manage(User $user): bool
    {
        return false;
    }
}
