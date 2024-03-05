<?php

namespace Javaabu\Auth\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Javaabu\Auth\Enums\UserStatuses;

class UserFactory extends Factory
{
    protected $model = \Javaabu\Auth\Models\User::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'password',
            'remember_token' => Str::random(10),
            'status' => $this->faker->randomElement(UserStatuses::getKeys()),
            'email_verified_at' => $this->faker->randomElement([now(), null]),
        ];
    }

    public function suspended(): UserFactory
    {
        return $this->state(function (array $attributes) {
            return [
                'account_status' => 'suspended',
            ];
        });
    }

    public function unverified(): UserFactory
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }

    public function verified(): UserFactory
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => now(),
            ];
        });
    }

    public function active(): UserFactory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => UserStatuses::APPROVED,
                'email_verified_at' => now(),
            ];
        });
    }

    public function requirePasswordUpdate(): UserFactory
    {
        return $this->state(function (array $attributes) {
            return [
                'require_password_update' => true,
            ];
        });
    }

    public function banned(): UserFactory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => UserStatuses::BANNED,
            ];
        });
    }

    public function pending(): UserFactory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => UserStatuses::PENDING,
            ];
        });
    }
}
