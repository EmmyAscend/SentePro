<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\Business;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => UserRole::Staff,
            'business_id' => null,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::SuperAdmin,
            'business_id' => null,
        ]);
    }

    /**
     * Business admin for the given business (or a newly created one if none is passed).
     */
    public function businessAdmin(?Business $business = null): static
    {
        return $this->state(['role' => UserRole::BusinessAdmin])
            ->for($business ?? Business::factory(), 'business');
    }

    /**
     * Staff member for the given business (or a newly created one if none is passed),
     * optionally with a set of granted permission abilities.
     */
    public function staff(?Business $business = null, array $permissions = []): static
    {
        return $this->state(['role' => UserRole::Staff, 'permissions' => $permissions])
            ->for($business ?? Business::factory(), 'business');
    }
}
