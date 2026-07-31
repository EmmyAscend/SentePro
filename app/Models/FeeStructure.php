<?php

namespace App\Models;

use App\Enums\PaymentProvider;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeStructure extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider',
        'business_id',
        'status',
        'gateway_fee_percent',
        'gateway_fee_flat',
        'platform_fee_percent',
        'platform_fee_flat',
        'min_fee',
        'max_fee',
    ];

    protected $casts = [
        'provider' => PaymentProvider::class,
        'gateway_fee_percent' => 'decimal:2',
        'gateway_fee_flat' => 'decimal:2',
        'platform_fee_percent' => 'decimal:2',
        'platform_fee_flat' => 'decimal:2',
        'min_fee' => 'decimal:2',
        'max_fee' => 'decimal:2',
    ];

    public function isEnabled(): bool
    {
        return $this->status === 'enabled';
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
