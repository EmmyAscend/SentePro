<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GatewayLog extends Model
{
    use HasFactory;

    protected $fillable = [
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

    public function gatewayProvider(): BelongsTo
    {
        return $this->belongsTo(GatewayProvider::class);
    }
}
