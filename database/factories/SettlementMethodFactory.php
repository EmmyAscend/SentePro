<?php

namespace Database\Factories;

use App\Models\SettlementMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SettlementMethod>
 */
class SettlementMethodFactory extends Factory
{
    protected $model = SettlementMethod::class;

    public function definition(): array
    {
        return [
            'code' => 'method-'.$this->faker->unique()->numerify('###'),
            'name' => $this->faker->words(2, true),
            'status' => 'enabled',
            'processing_time' => 1,
            'time_unit' => 'days',
            'settlement_fee_percent' => 0,
            'settlement_fee_flat' => 0,
            'platform_fee_percent' => 0,
            'platform_fee_flat' => 0,
            'weekend_processing' => true,
        ];
    }
}
