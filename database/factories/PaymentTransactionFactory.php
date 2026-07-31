<?php

namespace Database\Factories;

use App\Models\PaymentTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentTransactionFactory extends Factory
{
    protected $model = PaymentTransaction::class;

    public function definition(): array
    {
        return [
            'business_id' => 1,
            'provider' => 'pesapal',
            'amount' => 7500,
            'currency' => 'UGX',
            'status' => 'processing',
            'external_reference' => 'txn-'.fake()->uuid(),
        ];
    }
}
