<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Business;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class BusinessRegistrationService
{
    /**
     * Register a business and its owning business_admin account together, so a
     * business registration always results in a working login for that business.
     */
    public function register(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $business = Business::create([
                'business_name' => $data['business_name'],
                'business_type' => $data['business_type'],
                'trading_name' => $data['trading_name'] ?? null,
                'registration_number' => $data['registration_number'] ?? null,
                'id_number' => $data['id_number'] ?? null,
                'country' => $data['country'],
                'phone' => $data['phone'],
                'email' => $data['email'],
                'industry' => $data['industry'] ?? null,
                'expected_monthly_volume' => $data['expected_monthly_volume'] ?? null,
                'business_description' => $data['business_description'] ?? null,
                'status' => 'pending',
            ]);

            $owner = User::create([
                'name' => $data['owner_name'],
                'email' => $data['owner_email'],
                'password' => Hash::make($data['owner_password']),
                'role' => UserRole::BusinessAdmin,
                'business_id' => $business->id,
            ]);

            event(new Registered($owner));

            return $owner;
        });
    }
}
