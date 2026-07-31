<?php

namespace App\Services;

use App\Contracts\PaymentGatewayDriver;
use App\Enums\PaymentProvider;
use App\Services\Gateways\PesapalDriver;
use App\Services\Gateways\YoPaymentsDriver;

class PaymentGatewayManager
{
    public function driver(PaymentProvider $provider): PaymentGatewayDriver
    {
        return match ($provider) {
            PaymentProvider::Pesapal => app(PesapalDriver::class),
            PaymentProvider::YoPayments => app(YoPaymentsDriver::class),
        };
    }
}
