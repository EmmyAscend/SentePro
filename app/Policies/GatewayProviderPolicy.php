<?php

namespace App\Policies;

use App\Models\User;
use App\Policies\Concerns\AuthorizesTenantAccess;

class GatewayProviderPolicy
{
    use AuthorizesTenantAccess;

    /**
     * The cross-tenant health/log monitoring dashboard — super-admin-only via
     * the Gate::before bypass, same always-false-in-body pattern as
     * BusinessPolicy/AuditLogPolicy/WalletPolicy. Testing a business's own
     * already-configured gateway is separate, self-service diagnostics that
     * reuses the trait's own `update` ability instead.
     */
    public function manage(User $user): bool
    {
        return false;
    }
}
