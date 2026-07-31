<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesTenantAccess;

class GatewayProviderPolicy
{
    use AuthorizesTenantAccess;
}
