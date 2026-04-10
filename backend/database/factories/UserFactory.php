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
            'role_id' => Role::where('role_code', 'student')->value('role_id'),
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

            if (! Role::where('role_id', $user->role_id)->exists()) {
                Role::create([
                    'role_id' => $user->role_id,
                    'role_code' => 'student',
                    'role_name' => 'Student',
                    'is_active' => true,
                ]);
            }
        })->afterCreating(function (User $user) {
            UserAccount::factory()->create([
                'user_id' => $user->user_id,
            ]);
        });
    }

    public function instructor(): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => Role::where('role_code', 'instructor')->value('role_id'),
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => Role::where('role_code', 'admin')->value('role_id'),
        ]);
    }
}
