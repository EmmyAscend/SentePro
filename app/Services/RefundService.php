<?php

namespace App\Services;

use App\Enums\PaymentProvider;
use App\Enums\PaymentTransactionStatus;
use App\Enums\RefundStatus;
use App\Models\FeeBreakdown;
use App\Models\GatewayProvider;
use App\Models\PaymentTransaction;
use App\Models\Refund;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RefundService
{
    public function __construct(
        private readonly AuditLogService $auditLog,
        private readonly PaymentGatewayManager $gatewayManager,
    ) {}

    /**
     * Refund a completed transaction in full. Guards run before any external
     * call; the gateway call itself happens outside DB::transaction() since
     * it's network I/O and shouldn't hold a wallet lock open. A failed
     * gateway response is still recorded as a Refund row (for support/audit)
     * before being surfaced to the caller as a validation error.
     */
    public function refund(PaymentTransaction $transaction, User $requestedBy, ?string $reason = null): Refund
    {
        $this->assertRefundable($transaction);

        $gatewayProvider = $this->resolveGatewayProvider($transaction);

        $driver = $this->gatewayManager->driver($transaction->provider);
        $result = $driver->refund($transaction, (float) $transaction->amount, $gatewayProvider);

        $netAmountReversed = $this->resolveNetAmount($transaction);
        $succeeded = $result['status'] === 'completed';

        $refund = DB::transaction(function () use ($transaction, $requestedBy, $reason, $result, $netAmountReversed, $succeeded) {
            $refund = Refund::create([
                'business_id' => $transaction->business_id,
                'payment_transaction_id' => $transaction->id,
                'requested_by' => $requestedBy->id,
                'amount' => $transaction->amount,
                'net_amount_reversed' => $succeeded ? $netAmountReversed : 0,
                'status' => $succeeded ? RefundStatus::Completed : RefundStatus::Failed,
                'reason' => $reason,
                'gateway_response' => $result['raw'],
            ]);

            if ($succeeded) {
                $wallet = $transaction->business->wallet()->lockForUpdate()->first();

                $wallet->update([
                    'available_balance' => $wallet->available_balance - $netAmountReversed,
                    'settlement_balance' => $wallet->settlement_balance - $netAmountReversed,
                ]);

                $transaction->update(['status' => PaymentTransactionStatus::Refunded]);

                $this->auditLog->record('transaction.refunded', $transaction, [
                    'net_amount_reversed' => (string) $netAmountReversed,
                ]);
            } else {
                $this->auditLog->record('transaction.refund_failed', $transaction, []);
            }

            return $refund;
        });

        if (! $succeeded) {
            throw ValidationException::withMessages([
                'transaction' => 'The gateway declined the refund request.',
            ]);
        }

        return $refund;
    }

    private function assertRefundable(PaymentTransaction $transaction): void
    {
        if ($transaction->status !== PaymentTransactionStatus::Completed) {
            throw ValidationException::withMessages([
                'transaction' => 'Only completed transactions can be refunded.',
            ]);
        }

        if ($transaction->provider === PaymentProvider::YoPayments) {
            throw ValidationException::withMessages([
                'transaction' => 'Yo Payments does not support refunds — no reversal API exists for mobile money collections.',
            ]);
        }
    }

    private function resolveGatewayProvider(PaymentTransaction $transaction): GatewayProvider
    {
        $gatewayProvider = GatewayProvider::query()
            ->where('business_id', $transaction->business_id)
            ->where('provider', $transaction->provider)
            ->where('status', 'active')
            ->first();

        if (! $gatewayProvider) {
            throw ValidationException::withMessages([
                'transaction' => 'No active gateway is configured for this provider.',
            ]);
        }

        return $gatewayProvider;
    }

    private function resolveNetAmount(PaymentTransaction $transaction): float
    {
        $netAmount = FeeBreakdown::query()
            ->where('business_id', $transaction->business_id)
            ->where('transaction_reference', $transaction->external_reference)
            ->latest()
            ->value('net_amount');

        return $netAmount !== null ? (float) $netAmount : (float) $transaction->amount;
    }
}
