<?php

namespace App\Enums;

enum BusinessType: string
{
    case Individual = 'individual';
    case Business = 'business';
    case Ngo = 'ngo';

    public function label(): string
    {
        return match ($this) {
            self::Individual => 'Individual',
            self::Business => 'Business',
            self::Ngo => 'Non-Profit Organisation',
        };
    }
}
