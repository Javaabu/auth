<?php

namespace Javaabu\Auth\Tests\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Javaabu\Auth\Enums\UserStatuses;
use Javaabu\Auth\Tests\Models\Admin;

/**
 * @extends Factory<Admin>
 */
class AdminFactory extends Factory
{
    protected $model = Admin::class;

    public function definition(): array
    {
        return [
            'name' => 'އަޙްމަދު ޢަލީ',
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => 'password',
            'status' => UserStatuses::APPROVED,
        ];
    }

    public function unverified(): static
    {
        return $this->state(['email_verified_at' => null]);
    }

    public function pending(): static
    {
        return $this->state(['status' => UserStatuses::PENDING]);
    }

    public function banned(): static
    {
        return $this->state(['status' => UserStatuses::BANNED]);
    }

    public function withTwoFactor(string $secret = 'JBSWY3DPEHPK3PXP'): static
    {
        return $this->state([
            'two_factor_secret' => encrypt($secret),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1', 'recovery-code-2'])),
            'two_factor_confirmed_at' => now(),
        ]);
    }
}
