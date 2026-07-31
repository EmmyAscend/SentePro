<?php

namespace App\Policies;

use App\Models\PaymentTransaction;
use App\Models\User;
use App\Policies\Concerns\AuthorizesTenantAccess;

class PaymentTransactionPolicy
{
    use AuthorizesTenantAccess;

    /**
     * Refunding is business self-service, same tier as SettlementPolicy::create() —
     * business_admin/super_admin always pass, staff need the ability explicitly granted.
     */
    public function refund(User $user, PaymentTransaction $transaction): bool
    {
        return $user->business_id === $transaction->business_id && $user->hasPermission('transactions.refund');
    }
}
