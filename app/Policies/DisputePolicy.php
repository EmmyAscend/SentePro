<?php

namespace App\Policies;

use App\Models\Dispute;
use App\Models\PaymentTransaction;
use App\Models\User;
use App\Policies\Concerns\AuthorizesTenantAccess;

class DisputePolicy
{
    use AuthorizesTenantAccess;

    /**
     * Opening a dispute is business self-service, same tier as opening a
     * support ticket — no staff-permission gate needed.
     */
    public function create(User $user, PaymentTransaction $transaction): bool
    {
        return $user->business_id !== null && $user->business_id === $transaction->business_id;
    }

    /**
     * Resolve/reject a dispute. Only super admins may process disputes
     * (granted via the Gate::before bypass) — the business that raised a
     * dispute can't decide its own outcome, unlike a support ticket it can
     * resolve itself.
     */
    public function process(User $user, Dispute $dispute): bool
    {
        return false;
    }
}
