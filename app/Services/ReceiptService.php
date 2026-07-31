<?php

namespace App\Services;

use App\Mail\ReceiptMail;
use App\Models\PaymentTransaction;
use App\Models\Receipt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ReceiptService
{
    /**
     * @param  array{gatewayFee: float, platformFee: float, totalFee: float, netAmount: float}  $feeBreakdown
     */
    public function generate(PaymentTransaction $transaction, array $feeBreakdown): Receipt
    {
        $receipt = Receipt::create([
            'business_id' => $transaction->business_id,
            'payment_transaction_id' => $transaction->id,
            'reference_number' => 'RCPT-'.Str::upper(Str::random(10)),
            'amount' => $transaction->amount,
            'net_amount' => $feeBreakdown['netAmount'],
            'currency' => $transaction->currency,
            'customer_name' => $transaction->customer_name,
            'customer_email' => $transaction->customer_email,
        ]);

        if ($receipt->customer_email) {
            Mail::to($receipt->customer_email)->queue(new ReceiptMail($receipt));
            // "Queued", not "delivered" — there's no provider webhook wired up
            // to confirm actual delivery.
            $receipt->update(['emailed_at' => now()]);
        }

        return $receipt;
    }
}
