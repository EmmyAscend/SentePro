<?php

namespace App\Policies;

use App\Models\Settlement;
use App\Models\User;
use App\Policies\Concerns\AuthorizesTenantAccess;

class SettlementPolicy
{
    use AuthorizesTenantAccess;

    public function create(User $user): bool
    {
        return $user->business_id !== null && $user->hasPermission('settlements.create');
    }

    /**
     * Complete/reject a settlement. Only super admins may process settlements
     * (granted via the Gate::before bypass) — no business-tier role holds this
     * ability, same as BusinessPolicy::review().
     */
    public function process(User $user, Settlement $settlement): bool
    {
        return false;
    }
}
