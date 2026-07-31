<?php

namespace App\Models;

use App\Enums\RefundStatus;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refund extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'business_id',
        'payment_transaction_id',
        'requested_by',
        'amount',
        'net_amount_reversed',
        'status',
        'reason',
        'gateway_response',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'net_amount_reversed' => 'decimal:2',
        'status' => RefundStatus::class,
        'gateway_response' => 'array',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function paymentTransaction(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}
