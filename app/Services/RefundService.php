<?php

namespace App\Services;

use App\Enums\PaymentProvider;
use App\Enums\PaymentTransactionStatus;
use App\Enums\RefundStatus;
use App\Jobs\SendSmsNotification;
use App\Mail\RefundProcessedMail;
use App\Models\FeeBreakdown;
use App\Models\GatewayProvider;
use App\Models\PaymentTransaction;
use App\Models\Refund;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class RefundService
{
    public function __construct(
        private readonly AuditLogService $auditLog,
        private readonly PaymentGatewayManager $gatewayManager,
    ) {}

    /**
     * Refund a transaction, in full or in part. Omitting $amount refunds
     * whatever remains outstanding — the original "always full refund"
     * behavior. Guards run before any external call; the gateway call itself
     * happens outside DB::transaction() since it's network I/O and shouldn't
     * hold a wallet lock open. A failed gateway response is still recorded
     * as a Refund row (for support/audit) before being surfaced to the
     * caller as a validation error.
     */
    public function refund(PaymentTransaction $transaction, User $requestedBy, ?float $amount = null, ?string $reason = null): Refund
    {
        $amount ??= $transaction->remainingRefundableAmount();

        $this->assertRefundable($transaction, $amount);

        $gatewayProvider = $this->resolveGatewayProvider($transaction);

        $driver = $this->gatewayManager->driver($transaction->provider);
        $result = $driver->refund($transaction, $amount, $gatewayProvider);

        $netAmountReversed = $this->proportionalNetAmount($transaction, $amount);
        $succeeded = $result['status'] === 'completed';

        $refund = DB::transaction(function () use ($transaction, $requestedBy, $reason, $result, $amount, $netAmountReversed, $succeeded) {
            $refund = Refund::create([
                'business_id' => $transaction->business_id,
                'payment_transaction_id' => $transaction->id,
                'requested_by' => $requestedBy->id,
                'amount' => $amount,
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

                $transaction->update([
                    'status' => $transaction->remainingRefundableAmount() > 0
                        ? PaymentTransactionStatus::PartiallyRefunded
                        : PaymentTransactionStatus::Refunded,
                ]);

                $this->auditLog->record('transaction.refunded', $transaction, [
                    'amount' => (string) $amount,
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

        if ($transaction->customer_email) {
            Mail::to($transaction->customer_email)->queue(new RefundProcessedMail($refund));
        }

        if ($transaction->customer_phone) {
            SendSmsNotification::dispatch(
                $transaction->customer_phone,
                "Hi, your payment of {$transaction->currency} ".number_format((float) $refund->amount, 2)." to {$transaction->business->business_name} has been refunded.",
            );
        }

        return $refund;
    }

    private function assertRefundable(PaymentTransaction $transaction, float $amount): void
    {
        if (! in_array($transaction->status, [PaymentTransactionStatus::Completed, PaymentTransactionStatus::PartiallyRefunded], true)) {
            throw ValidationException::withMessages([
                'transaction' => 'Only completed transactions can be refunded.',
            ]);
        }

        if ($transaction->provider === PaymentProvider::YoPayments) {
            throw ValidationException::withMessages([
                'transaction' => 'Yo Payments does not support refunds — no reversal API exists for mobile money collections.',
            ]);
        }

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'The refund amount must be greater than zero.',
            ]);
        }

        $remaining = $transaction->remainingRefundableAmount();

        if ($amount > $remaining) {
            throw ValidationException::withMessages([
                'amount' => 'The refund amount exceeds the remaining refundable balance of '.number_format($remaining, 2).'.',
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

    /**
     * Scales the transaction's original net (post-fee) amount by the share of
     * the gross amount being refunded — a full refund (amount === transaction
     * amount) reverses exactly the original net amount, same as before partial
     * refunds existed.
     */
    private function proportionalNetAmount(PaymentTransaction $transaction, float $amount): float
    {
        $originalNet = FeeBreakdown::query()
            ->where('business_id', $transaction->business_id)
            ->where('transaction_reference', $transaction->external_reference)
            ->latest()
            ->value('net_amount');

        $originalNet = $originalNet !== null ? (float) $originalNet : (float) $transaction->amount;

        if ((float) $transaction->amount <= 0) {
            return 0.0;
        }

        return round($originalNet * ($amount / (float) $transaction->amount), 2);
    }
}
