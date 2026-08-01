<?php

namespace App\Services;

use App\Contracts\PaymentGatewayDriver;
use App\Enums\PaymentProvider;
use App\Services\Gateways\LoggingGatewayDriver;
use App\Services\Gateways\PesapalDriver;
use App\Services\Gateways\YoPaymentsDriver;

class PaymentGatewayManager
{
    public function driver(PaymentProvider $provider): PaymentGatewayDriver
    {
        $driver = match ($provider) {
            PaymentProvider::Pesapal => app(PesapalDriver::class),
            PaymentProvider::YoPayments => app(YoPaymentsDriver::class),
        };

        return new LoggingGatewayDriver($driver);
    }
}
