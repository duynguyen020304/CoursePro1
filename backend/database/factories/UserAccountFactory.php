<?php

namespace Database\Factories;

use App\Models\UserAccount;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<UserAccount>
 */
class UserAccountFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'user_id' => Str::uuid(), // Will be overridden when created with User
            'provider' => 'email',
            'provider_account_id' => null,
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'is_deleted' => false,
            'is_verified' => true,
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
            'is_verified' => false,
        ]);
    }

    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_deleted' => true,
        ]);
    }

    public function oauth(string $provider = 'google'): static
    {
        return $this->state(fn (array $attributes) => [
            'provider' => $provider,
            'provider_account_id' => Str::uuid(),
            'password' => null,
        ]);
    }
}