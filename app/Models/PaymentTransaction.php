<?php

namespace App\Models;

use App\Enums\PaymentProvider;
use App\Enums\PaymentTransactionStatus;
use App\Models\Concerns\BelongsToTenant;
use App\Services\ReceiptService;
use App\Services\TransactionFeeService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'payment_transactions';

    protected $fillable = [
        'business_id',
        'payment_link_id',
        'provider',
        'amount',
        'currency',
        'status',
        'external_reference',
        'provider_reference',
        'customer_name',
        'customer_email',
        'customer_phone',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'provider' => PaymentProvider::class,
        'status' => PaymentTransactionStatus::class,
    ];

    protected static function booted(): void
    {
        static::created(function (self $transaction): void {
            if ($transaction->status === PaymentTransactionStatus::Completed) {
                static::applyFeeAndCredit($transaction);
            }
        });

        static::updated(function (self $transaction): void {
            if ($transaction->isDirty('status') && $transaction->status === PaymentTransactionStatus::Completed) {
                static::applyFeeAndCredit($transaction);
            }
        });
    }

    /**
     * Resolves the applicable fee structure (if any), records a FeeBreakdown
     * for it, and credits the wallet with the net (post-fee) amount rather
     * than the gross transaction amount — see App\Services\TransactionFeeService.
     * A transaction with no matching fee structure is charged nothing, so
     * this is a strict superset of the previous "credit the full amount"
     * behavior, not a breaking change for flows that don't configure fees.
     */
    private static function applyFeeAndCredit(self $transaction): void
    {
        $feeService = app(TransactionFeeService::class);
        $feeStructure = $feeService->resolve($transaction->provider, $transaction->business);

        $breakdown = $feeStructure
            ? $feeService->calculate($feeStructure, (float) $transaction->amount)
            : ['gatewayFee' => 0.0, 'platformFee' => 0.0, 'totalFee' => 0.0, 'netAmount' => (float) $transaction->amount];

        FeeBreakdown::create([
            'business_id' => $transaction->business_id,
            'transaction_reference' => $transaction->external_reference,
            'gateway_fee' => $breakdown['gatewayFee'],
            'platform_fee' => $breakdown['platformFee'],
            'net_amount' => $breakdown['netAmount'],
        ]);

        $transaction->business->wallet()->update([
            'available_balance' => $transaction->business->wallet->available_balance + $breakdown['netAmount'],
            'settlement_balance' => $transaction->business->wallet->settlement_balance + $breakdown['netAmount'],
        ]);

        app(ReceiptService::class)->generate($transaction, $breakdown);
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function paymentLink(): BelongsTo
    {
        return $this->belongsTo(PaymentLink::class);
    }
}
