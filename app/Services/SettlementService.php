<?php

namespace App\Services;

use App\Enums\SettlementStatus;
use App\Models\Business;
use App\Models\Settlement;
use App\Models\SettlementMethod;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SettlementService
{
    public function __construct(
        private readonly AuditLogService $auditLog,
        private readonly SettlementEstimateService $estimateService,
    ) {}

    /**
     * Request a settlement, reserving the amount out of the wallet's available
     * balance into its pending balance, and locking in the fee breakdown and
     * estimated completion date from the chosen method's current configuration.
     */
    public function request(Business $business, SettlementMethod $method, array $data): Settlement
    {
        return DB::transaction(function () use ($business, $method, $data) {
            $amount = (float) $data['amount'];

            if (! $method->isEnabled()) {
                throw ValidationException::withMessages([
                    'settlement_method_id' => 'The selected settlement method is not currently available.',
                ]);
            }

            if ($method->min_amount !== null && $amount < (float) $method->min_amount) {
                throw ValidationException::withMessages([
                    'amount' => 'The minimum settlement amount for this method is '.number_format((float) $method->min_amount, 2).'.',
                ]);
            }

            if ($method->max_amount !== null && $amount > (float) $method->max_amount) {
                throw ValidationException::withMessages([
                    'amount' => 'The maximum settlement amount for this method is '.number_format((float) $method->max_amount, 2).'.',
                ]);
            }

            if ($method->daily_limit !== null) {
                $requestedToday = Settlement::query()
                    ->where('business_id', $business->id)
                    ->where('settlement_method_id', $method->id)
                    ->whereDate('created_at', now()->toDateString())
                    ->whereNotIn('status', [SettlementStatus::Rejected, SettlementStatus::Cancelled])
                    ->sum('amount');

                if ($requestedToday + $amount > (float) $method->daily_limit) {
                    throw ValidationException::withMessages([
                        'amount' => 'This request would exceed the daily settlement limit for this method.',
                    ]);
                }
            }

            $wallet = $business->wallet()->lockForUpdate()->first();

            if (! $wallet) {
                throw ValidationException::withMessages([
                    'business_id' => 'The selected business does not have a wallet yet.',
                ]);
            }

            if ($amount > (float) $wallet->available_balance) {
                throw ValidationException::withMessages([
                    'amount' => 'The requested amount exceeds the available wallet balance.',
                ]);
            }

            $estimate = $this->estimateService->estimate($method, $amount);

            $wallet->update([
                'available_balance' => $wallet->available_balance - $amount,
                'pending_balance' => $wallet->pending_balance + $amount,
            ]);

            $settlement = Settlement::create([
                'business_id' => $business->id,
                'settlement_method_id' => $method->id,
                'amount' => $amount,
                'gateway_fee' => $estimate['gatewayFee'],
                'platform_fee' => $estimate['platformFee'],
                'net_amount' => $estimate['netAmount'],
                'estimated_completion_at' => $estimate['estimatedCompletionAt'],
                'status' => SettlementStatus::Pending,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->auditLog->record('settlement.requested', $settlement, ['amount' => (string) $amount]);

            return $settlement;
        });
    }

    /**
     * Complete a settlement: releases the reserved amount out of pending_balance
     * and credits the net (post-fee) amount into settlement_balance. The fee
     * portion is platform revenue — it doesn't land back in any wallet bucket.
     */
    public function complete(Settlement $settlement): Settlement
    {
        return DB::transaction(function () use ($settlement) {
            $this->assertProcessable($settlement);

            $wallet = $settlement->business->wallet()->lockForUpdate()->first();

            $wallet->update([
                'pending_balance' => $wallet->pending_balance - $settlement->amount,
                'settlement_balance' => $wallet->settlement_balance + $settlement->net_amount,
            ]);

            $settlement->update(['status' => SettlementStatus::Completed]);

            $this->auditLog->record('settlement.completed', $settlement, ['net_amount' => (string) $settlement->net_amount]);

            return $settlement->fresh();
        });
    }

    /**
     * Reject a settlement: fully reverses the reservation, returning the amount
     * from pending_balance back to available_balance.
     */
    public function reject(Settlement $settlement, ?string $reason = null): Settlement
    {
        return DB::transaction(function () use ($settlement, $reason) {
            $this->assertProcessable($settlement);

            $wallet = $settlement->business->wallet()->lockForUpdate()->first();

            $wallet->update([
                'pending_balance' => $wallet->pending_balance - $settlement->amount,
                'available_balance' => $wallet->available_balance + $settlement->amount,
            ]);

            $settlement->update(['status' => SettlementStatus::Rejected]);

            $this->auditLog->record('settlement.rejected', $settlement, ['reason' => $reason]);

            return $settlement->fresh();
        });
    }

    private function assertProcessable(Settlement $settlement): void
    {
        if (! in_array($settlement->status, [SettlementStatus::Pending, SettlementStatus::Processing], true)) {
            throw ValidationException::withMessages([
                'status' => 'Only pending or processing settlements can be processed.',
            ]);
        }
    }
}
