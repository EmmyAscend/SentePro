<?php

namespace App\Services;

use App\Enums\SettlementTimeUnit;
use App\Models\BusinessCalendar;
use App\Models\PublicHoliday;
use App\Models\SettlementMethod;
use Carbon\CarbonImmutable;

class SettlementEstimateService
{
    /**
     * @return array{gatewayFee: float, platformFee: float, totalFee: float, netAmount: float, estimatedCompletionAt: CarbonImmutable}
     */
    public function estimate(SettlementMethod $method, float $amount, ?CarbonImmutable $from = null): array
    {
        $gatewayFee = round($amount * (float) $method->settlement_fee_percent / 100 + (float) $method->settlement_fee_flat, 2);
        $platformFee = round($amount * (float) $method->platform_fee_percent / 100 + (float) $method->platform_fee_flat, 2);

        return [
            'gatewayFee' => $gatewayFee,
            'platformFee' => $platformFee,
            'totalFee' => round($gatewayFee + $platformFee, 2),
            'netAmount' => round($amount - $gatewayFee - $platformFee, 2),
            'estimatedCompletionAt' => $this->calculateCompletionAt($method, $from ?? CarbonImmutable::now()),
        ];
    }

    /**
     * "Hours" is literal calendar-hour addition (no business-hours stepping).
     * "Days"/"working_days" step forward one calendar day at a time, skipping
     * non-working-days and public holidays unless the method allows weekend
     * processing.
     */
    public function calculateCompletionAt(SettlementMethod $method, CarbonImmutable $from): CarbonImmutable
    {
        $calendar = BusinessCalendar::current();
        $cursor = $from;

        if ($cursor->format('H:i:s') > $calendar->cutoff_time) {
            $cursor = $cursor->addDay()->setTimeFromTimeString($calendar->business_hours_start);
        }

        if ($method->time_unit === SettlementTimeUnit::Hours) {
            return $cursor->addHours($method->processing_time);
        }

        $remaining = $method->processing_time;

        while ($remaining > 0) {
            $cursor = $cursor->addDay();

            if ($this->isWorkingDay($cursor, $calendar, $method)) {
                $remaining--;
            }
        }

        return $cursor;
    }

    private function isWorkingDay(CarbonImmutable $date, BusinessCalendar $calendar, SettlementMethod $method): bool
    {
        if ($method->weekend_processing) {
            return true;
        }

        if (! in_array($date->isoWeekday(), $calendar->working_days, true)) {
            return false;
        }

        return ! PublicHoliday::query()->whereDate('date', $date->toDateString())->exists();
    }
}
