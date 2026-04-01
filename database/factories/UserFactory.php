<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function configure(): static
    {
        return $this->afterMaking(function (User $user): void {
            if (! array_key_exists('password', $user->getAttributes())) {
                $user->setAttribute('password', static::$password ??= Hash::make('password'));
            }

            if (! array_key_exists('must_change_password', $user->getAttributes())) {
                $user->setAttribute('must_change_password', false);
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'username' => fake()->unique()->userName().'_'.fake()->numerify('####'),
            'email' => fake()->boolean(70) ? fake()->unique()->safeEmail() : null,
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
            'role' => UserRole::Employee,
            'is_active' => true,
            'can_view_all_kyc' => false,
            'can_view_reports' => false,
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Admin,
            'can_view_all_kyc' => true,
            'can_view_reports' => true,
        ]);
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
