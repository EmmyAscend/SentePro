<?php

namespace Database\Factories;

use App\Models\PaymentLink;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentLinkFactory extends Factory
{
    protected $model = PaymentLink::class;

    public function definition(): array
    {
        return [
            'business_id' => 1,
            'title' => $this->faker->sentence(3),
            'type' => 'donation',
            'amount' => 2500,
            'currency' => 'UGX',
            'custom_amount' => true,
            'expiry_date' => now()->addDays(7),
            'description' => $this->faker->sentence(),
            'status' => 'active',
        ];
    }
}
