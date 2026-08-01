<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_business_id',
        'recipient_business_id',
        'initiated_by',
        'reference',
        'amount',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function senderBusiness(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'sender_business_id');
    }

    public function recipientBusiness(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'recipient_business_id');
    }

    public function initiatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }
}
