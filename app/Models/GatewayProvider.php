<?php

namespace App\Models;

use App\Enums\PaymentProvider;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GatewayProvider extends Model
{
    use HasFactory;

    protected $table = 'gateway_providers';

    protected $fillable = [
        'provider',
        'status',
        'environment',
        'webhook_url',
        'credentials',
        'supported_currencies',
        'last_checked_at',
        'last_health_status',
        'last_latency_ms',
        'last_error',
    ];

    protected $casts = [
        'provider' => PaymentProvider::class,
        'credentials' => 'encrypted:array',
        'last_checked_at' => 'datetime',
        'last_latency_ms' => 'integer',
    ];

    public function getRouteKeyName(): string
    {
        return 'provider';
    }

    /**
     * The `provider` column is cast to the PaymentProvider enum, so
     * attribute access (what getRouteKey() uses by default) returns an
     * enum instance rather than a string — fine for incoming route-model
     * binding, but route() generation needs a real string to build the URL.
     */
    public function getRouteKey(): mixed
    {
        return $this->provider->value;
    }

    public function logs(): HasMany
    {
        return $this->hasMany(GatewayLog::class);
    }

    /**
     * Every payment provider is a single platform-wide row, created inactive
     * with sane defaults the first time it's needed — same lazy-default
     * pattern as LegalPage::bySlug()/BusinessCalendar::current(). A super
     * admin configures real credentials and flips it active via
     * /admin/gateway-providers.
     */
    public static function byProvider(PaymentProvider $provider): self
    {
        return static::query()->firstOrCreate(['provider' => $provider], [
            'status' => 'inactive',
            'environment' => 'sandbox',
            'credentials' => [],
            'supported_currencies' => 'UGX,KES',
            'webhook_url' => $provider === PaymentProvider::YoPayments
                ? route('webhooks.yo-payments.success')
                : route('webhooks.pesapal.receive'),
        ]);
    }
}
