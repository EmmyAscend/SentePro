<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GatewayLog extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'business_id',
        'gateway_provider_id',
        'method',
        'success',
        'latency_ms',
        'error',
    ];

    protected $casts = [
        'success' => 'boolean',
        'latency_ms' => 'integer',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function gatewayProvider(): BelongsTo
    {
        return $this->belongsTo(GatewayProvider::class);
    }
}
