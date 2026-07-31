<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesTenantAccess;

class WebhookEventPolicy
{
    use AuthorizesTenantAccess;
}
