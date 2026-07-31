<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\BusinessCalendar;
use App\Models\FeeStructure;
use App\Models\SettlementMethod;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        BusinessCalendar::current();

        FeeStructure::query()->firstOrCreate(
            ['provider' => 'pesapal', 'business_id' => null],
            [
                'status' => 'enabled',
                'gateway_fee_percent' => 3,
                'platform_fee_percent' => 1,
            ]
        );

        FeeStructure::query()->firstOrCreate(
            ['provider' => 'yo_payments', 'business_id' => null],
            [
                'status' => 'enabled',
                'gateway_fee_percent' => 2,
                'platform_fee_percent' => 1,
            ]
        );

        SettlementMethod::query()->firstOrCreate(
            ['code' => 'mobile_money'],
            [
                'name' => 'Mobile Money',
                'status' => 'enabled',
                'processing_time' => 24,
                'time_unit' => 'hours',
                'settlement_fee_percent' => 2,
                'platform_fee_percent' => 1,
                'weekend_processing' => false,
                'public_description' => 'Estimated settlement time: within 24 working hours.',
            ]
        );

        SettlementMethod::query()->firstOrCreate(
            ['code' => 'bank_transfer'],
            [
                'name' => 'Bank Transfer',
                'status' => 'enabled',
                'processing_time' => 3,
                'time_unit' => 'working_days',
                'settlement_fee_percent' => 1.5,
                'platform_fee_percent' => 1,
                'weekend_processing' => false,
                'public_description' => 'Estimated settlement time: within 3 working days.',
            ]
        );

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        User::factory()->superAdmin()->create([
            'name' => 'SentePro Super Admin',
            'email' => 'admin@sentepro.test',
        ]);

        $business = Business::factory()->create([
            'business_name' => 'Demo Merchant Ltd',
            'status' => 'approved',
        ]);

        User::factory()->businessAdmin($business)->create([
            'name' => 'Demo Business Admin',
            'email' => 'owner@sentepro.test',
        ]);

        User::factory()->staff($business, ['settlements.create'])->create([
            'name' => 'Demo Staff Member',
            'email' => 'staff@sentepro.test',
        ]);
    }
}
