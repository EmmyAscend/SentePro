<?php

namespace App\Models;

use App\Enums\PaymentProvider;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GatewayProvider extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'gateway_providers';

    protected $fillable = [
        'business_id',
        'name',
        'provider',
        'status',
        'environment',
        'webhook_url',
        'credentials',
        'supported_countries',
        'supported_currencies',
        'last_checked_at',
        'last_health_status',
        'last_latency_ms',
        'last_error',
    ];

    protected $casts = [
        'provider' => PaymentProvider::class,
        'credentials' => 'encrypted:array',
        'last_checked_at' => 'datetime',
        'last_latency_ms' => 'integer',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(GatewayLog::class);
    }
}
