<?php

namespace App\Models;

use App\Enums\PaymentProvider;
use App\Enums\PaymentTransactionStatus;
use App\Enums\RefundStatus;
use App\Jobs\SendSmsNotification;
use App\Mail\PaymentReceivedMail;
use App\Models\Concerns\BelongsToTenant;
use App\Services\ReceiptService;
use App\Services\TransactionFeeService;
use App\Support\PhoneNumber;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Mail;

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
        'custom_field_values',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'provider' => PaymentProvider::class,
        'status' => PaymentTransactionStatus::class,
        'custom_field_values' => 'array',
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

        $receipt = app(ReceiptService::class)->generate($transaction, $breakdown);

        if ($transaction->customer_phone) {
            SendSmsNotification::dispatch(
                PhoneNumber::normalizeUganda($transaction->customer_phone),
                "Hi, we've received your payment of {$transaction->currency} ".number_format((float) $transaction->amount, 2)." to {$transaction->business->business_name}. Ref: {$receipt->reference_number}.",
            );
        }

        $recipients = $transaction->business->admins->pluck('email');

        if ($recipients->isNotEmpty()) {
            Mail::to($recipients)->queue(new PaymentReceivedMail($receipt));
        }
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function paymentLink(): BelongsTo
    {
        return $this->belongsTo(PaymentLink::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function receipt(): HasOne
    {
        return $this->hasOne(Receipt::class);
    }

    public function remainingRefundableAmount(): float
    {
        $refunded = $this->refunds()->where('status', RefundStatus::Completed)->sum('amount');

        return max(0.0, (float) $this->amount - (float) $refunded);
    }
}
