<?php

namespace App\Models;

use App\Enums\SettlementTimeUnit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SettlementMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'status',
        'processing_time',
        'time_unit',
        'settlement_fee_percent',
        'settlement_fee_flat',
        'platform_fee_percent',
        'platform_fee_flat',
        'min_amount',
        'max_amount',
        'daily_limit',
        'auto_approval',
        'weekend_processing',
        'public_description',
        'internal_notes',
    ];

    protected $casts = [
        'time_unit' => SettlementTimeUnit::class,
        'processing_time' => 'integer',
        'settlement_fee_percent' => 'decimal:2',
        'settlement_fee_flat' => 'decimal:2',
        'platform_fee_percent' => 'decimal:2',
        'platform_fee_flat' => 'decimal:2',
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'daily_limit' => 'decimal:2',
        'auto_approval' => 'boolean',
        'weekend_processing' => 'boolean',
    ];

    public function isEnabled(): bool
    {
        return $this->status === 'enabled';
    }

    public function settlements(): HasMany
    {
        return $this->hasMany(Settlement::class);
    }
}
