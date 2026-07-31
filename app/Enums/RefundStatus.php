<?php

namespace App\Enums;

enum RefundStatus: string
{
    case Completed = 'completed';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Completed => 'Completed',
            self::Failed => 'Failed',
        };
    }
}
