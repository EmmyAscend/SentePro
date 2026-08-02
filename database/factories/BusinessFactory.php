<?php

namespace Database\Factories;

use App\Models\Business;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Business>
 */
class BusinessFactory extends Factory
{
    protected $model = Business::class;

    public function definition(): array
    {
        return [
            'business_name' => $this->faker->company(),
            'business_type' => 'business',
            'trading_name' => $this->faker->company(),
            'registration_number' => strtoupper($this->faker->bothify('REG-#######')),
            'country' => 'Uganda',
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->safeEmail(),
            'industry' => 'Payments',
            'expected_monthly_volume' => '1000000',
            'business_description' => $this->faker->sentence(),
            'status' => 'pending',
        ];
    }
}
