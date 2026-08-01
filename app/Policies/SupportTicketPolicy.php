<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesTenantAccess;

class SupportTicketPolicy
{
    use AuthorizesTenantAccess;
}
