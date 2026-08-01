<?php

namespace App\Policies;

use App\Models\User;

class WalletTransferPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->business_id !== null;
    }

    /**
     * Sending money is business self-service, same tier as SettlementPolicy::create() —
     * business_admin/super_admin always pass, staff need the ability explicitly granted.
     * No super-admin "act on behalf of a business" path — a peer-to-peer transfer only
     * makes sense as something a business actually does.
     */
    public function create(User $user): bool
    {
        return $user->business_id !== null && $user->hasPermission('wallet-transfers.create');
    }
}
