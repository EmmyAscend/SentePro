<?php

namespace App\Models;

use App\Enums\SettlementStatus;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Settlement extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'business_id',
        'settlement_method_id',
        'amount',
        'gateway_fee',
        'platform_fee',
        'net_amount',
        'estimated_completion_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_fee' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'estimated_completion_at' => 'datetime',
        'status' => SettlementStatus::class,
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function settlementMethod(): BelongsTo
    {
        return $this->belongsTo(SettlementMethod::class);
    }
}
