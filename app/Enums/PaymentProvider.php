<?php

namespace App\Enums;

enum PaymentProvider: string
{
    case Pesapal = 'pesapal';
    case YoPayments = 'yo_payments';

    public function label(): string
    {
        return match ($this) {
            self::Pesapal => 'Pesapal',
            self::YoPayments => 'Yo Payments',
        };
    }
}
