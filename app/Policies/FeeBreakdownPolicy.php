<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesTenantAccess;

class FeeBreakdownPolicy
{
    use AuthorizesTenantAccess;
}
