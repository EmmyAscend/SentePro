<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesTenantAccess;

class PaymentTransactionPolicy
{
    use AuthorizesTenantAccess;
}
