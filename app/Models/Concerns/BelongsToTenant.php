<?php

namespace App\Models\Concerns;

use App\Models\Scopes\TenantScope;
use Illuminate\Support\Facades\Auth;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model) {
            if (empty($model->business_id) && Auth::check() && Auth::user()->business_id) {
                $model->business_id = Auth::user()->business_id;
            }
        });
    }
}
