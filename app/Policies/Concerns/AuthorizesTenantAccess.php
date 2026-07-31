<?php

namespace App\Policies\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Generic "does this user belong to the same business as this record" policy logic,
 * shared by every policy for a business-owned model. Super admins never reach these
 * methods because AppServiceProvider registers a Gate::before bypass for them.
 */
trait AuthorizesTenantAccess
{
    public function viewAny(User $user): bool
    {
        return $user->business_id !== null;
    }

    public function view(User $user, Model $model): bool
    {
        return $user->business_id !== null && $user->business_id === $model->business_id;
    }

    public function create(User $user): bool
    {
        return $user->business_id !== null;
    }

    public function update(User $user, Model $model): bool
    {
        return $user->business_id !== null && $user->business_id === $model->business_id;
    }
}
