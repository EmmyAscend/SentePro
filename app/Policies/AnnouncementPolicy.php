<?php

namespace App\Policies;

use App\Models\User;

class AnnouncementPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Authoring announcements is super-admin only, granted via the Gate::before
     * bypass — same always-false-in-body pattern as SettlementMethodPolicy.
     */
    public function manage(User $user): bool
    {
        return false;
    }
}
