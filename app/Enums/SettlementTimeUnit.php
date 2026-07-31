<?php

namespace App\Enums;

enum SettlementTimeUnit: string
{
    case Hours = 'hours';
    case Days = 'days';
    case WorkingDays = 'working_days';

    public function label(): string
    {
        return match ($this) {
            self::Hours => 'Hours',
            self::Days => 'Days',
            self::WorkingDays => 'Working Days',
        };
    }
}
