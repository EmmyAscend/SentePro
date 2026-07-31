<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeBreakdown extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'fee_breakdowns';

    protected $fillable = [
        'business_id',
        'transaction_reference',
        'gateway_fee',
        'platform_fee',
        'net_amount',
    ];

    protected $casts = [
        'gateway_fee' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'net_amount' => 'decimal:2',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
