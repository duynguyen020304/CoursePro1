<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserAccount;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'user_id' => Str::uuid(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'role_id' => 'student',
            'profile_image' => null,
        ];
    }

    /**
     * Configure the factory to create both User and UserAccount
     */
    public function configure(): static
    {
        return $this->afterMaking(function (User $user) {
            if (!$user->role_id) {
                return;
            }

            Role::firstOrCreate(
                ['role_id' => $user->role_id],
                ['role_name' => Str::headline($user->role_id)]
            );
        })->afterCreating(function (User $user) {
            UserAccount::factory()->create([
                'user_id' => $user->user_id,
            ]);
        });
    }

    public function instructor(): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => 'instructor',
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => 'admin',
        ]);
    }
}
