<?php

namespace App\Models;

use App\Enums\PaymentProvider;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    ];

    protected $casts = [
        'provider' => PaymentProvider::class,
        'credentials' => 'encrypted:array',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
