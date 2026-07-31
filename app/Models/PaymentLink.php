<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentLink extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'business_id',
        'title',
        'type',
        'amount',
        'custom_amount',
        'expiry_date',
        'description',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'custom_amount' => 'boolean',
        'expiry_date' => 'date',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
