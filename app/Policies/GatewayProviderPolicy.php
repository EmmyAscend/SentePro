<?php

namespace App\Policies;

use App\Models\User;

class GatewayProviderPolicy
{
    /**
     * Gateway credentials are platform-wide, super-admin-only configuration
     * now — granted entirely via the Gate::before bypass in
     * AppServiceProvider, same always-false-in-body pattern as
     * SettlementMethodPolicy/FeeStructurePolicy. There is no business-tier
     * ability at all anymore: viewing, editing, and testing a connection are
     * all the same "manage" concern.
     */
    public function manage(User $user): bool
    {
        return false;
    }
}
