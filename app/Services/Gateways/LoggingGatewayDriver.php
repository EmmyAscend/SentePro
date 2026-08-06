<?php

namespace App\Services\Gateways;

use App\Contracts\PaymentGatewayDriver;
use App\Models\GatewayLog;
use App\Models\GatewayProvider;
use App\Models\PaymentTransaction;
use Closure;
use Throwable;

/**
 * Wraps whichever real driver PaymentGatewayManager resolves, so every call
 * site (PaymentInitiationService, PaymentWebhookService, RefundService)
 * gets logging for free without any of them changing — the same "wrap once
 * at the resolution point" idea TenantScope already uses on models.
 */
class LoggingGatewayDriver implements PaymentGatewayDriver
{
    public function __construct(private readonly PaymentGatewayDriver $driver) {}

    public function initiate(PaymentTransaction $transaction, GatewayProvider $config): array
    {
        return $this->logged($config, 'initiate', fn () => $this->driver->initiate($transaction, $config));
    }

    public function checkStatus(string $providerReference, GatewayProvider $config): array
    {
        return $this->logged($config, 'checkStatus', fn () => $this->driver->checkStatus($providerReference, $config));
    }

    public function refund(PaymentTransaction $transaction, float $amount, GatewayProvider $config): array
    {
        return $this->logged($config, 'refund', fn () => $this->driver->refund($transaction, $amount, $config));
    }

    public function ping(GatewayProvider $config): array
    {
        return $this->logged($config, 'ping', fn () => $this->driver->ping($config));
    }

    /**
     * initiate/checkStatus/refund are real operations — a thrown exception
     * already means "this failed," so success there is simply "didn't
     * throw." ping() is designed to never throw, so its success comes from
     * the `healthy` flag it returns instead.
     */
    private function logged(GatewayProvider $config, string $method, Closure $call): array
    {
        $start = microtime(true);

        try {
            $result = $call();
            $latencyMs = (int) round((microtime(true) - $start) * 1000);

            $success = $method === 'ping' ? ($result['healthy'] ?? false) : true;
            $error = $method === 'ping' ? ($result['error'] ?? null) : null;

            $this->record($config, $method, $success, $latencyMs, $error);

            return $result;
        } catch (Throwable $e) {
            $this->record($config, $method, false, (int) round((microtime(true) - $start) * 1000), $e->getMessage());

            throw $e;
        }
    }

    private function record(GatewayProvider $config, string $method, bool $success, int $latencyMs, ?string $error): void
    {
        GatewayLog::create([
            'gateway_provider_id' => $config->id,
            'method' => $method,
            'success' => $success,
            'latency_ms' => $latencyMs,
            'error' => $error,
        ]);

        $config->update([
            'last_checked_at' => now(),
            'last_health_status' => $success ? 'healthy' : 'unhealthy',
            'last_latency_ms' => $latencyMs,
            'last_error' => $error,
        ]);
    }
}
