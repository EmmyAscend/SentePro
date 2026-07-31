<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookEvent extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'webhook_events';

    protected $fillable = [
        'business_id',
        'provider',
        'event',
        'payload',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
