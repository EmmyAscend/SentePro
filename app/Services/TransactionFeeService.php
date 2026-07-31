<?php

namespace App\Services;

use App\Enums\PaymentProvider;
use App\Models\Business;
use App\Models\FeeStructure;

class TransactionFeeService
{
    /**
     * A business-specific fee structure wins over the platform-wide default
     * for the same provider; if neither exists, no fee is charged at all.
     */
    public function resolve(PaymentProvider $provider, Business $business): ?FeeStructure
    {
        return FeeStructure::query()
            ->where('provider', $provider->value)
            ->where('status', 'enabled')
            ->where(function ($query) use ($business) {
                $query->where('business_id', $business->id)->orWhereNull('business_id');
            })
            ->orderByRaw('business_id IS NULL') // business-specific row sorts first
            ->first();
    }

    /**
     * min_fee/max_fee clamp the total fee by adjusting platform_fee only —
     * gateway_fee is modeled as the real pass-through cost to the gateway and
     * is never shrunk below what's actually owed.
     *
     * @return array{gatewayFee: float, platformFee: float, totalFee: float, netAmount: float}
     */
    public function calculate(FeeStructure $fee, float $amount): array
    {
        $gatewayFee = round($amount * (float) $fee->gateway_fee_percent / 100 + (float) $fee->gateway_fee_flat, 2);
        $platformFee = round($amount * (float) $fee->platform_fee_percent / 100 + (float) $fee->platform_fee_flat, 2);
        $totalFee = $gatewayFee + $platformFee;

        if ($fee->min_fee !== null && $totalFee < (float) $fee->min_fee) {
            $platformFee += (float) $fee->min_fee - $totalFee;
            $totalFee = (float) $fee->min_fee;
        }

        if ($fee->max_fee !== null && $totalFee > (float) $fee->max_fee) {
            $platformFee = max(0, $platformFee - ($totalFee - (float) $fee->max_fee));
            $totalFee = $gatewayFee + $platformFee;
        }

        return [
            'gatewayFee' => round($gatewayFee, 2),
            'platformFee' => round($platformFee, 2),
            'totalFee' => round($totalFee, 2),
            'netAmount' => round($amount - $totalFee, 2),
        ];
    }
}
