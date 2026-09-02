<?php

namespace Database\Factories\League;

use App\Domain\League\Enums\LeagueInvitationStatus;
use App\Models\League\League;
use App\Models\League\LeagueInvitation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeagueInvitation>
 */
class LeagueInvitationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'league_id' => League::factory(),
            'invited_by_user_id' => User::factory(),
            'email' => fake()->safeEmail(),
            'status' => LeagueInvitationStatus::PENDING,
            'expires_at' => now()->addDays(7),
            'accepted_at' => null,
            'revoked_at' => null,
        ];
    }

    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LeagueInvitationStatus::ACCEPTED,
            'accepted_at' => now(),
        ]);
    }

    public function revoked(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LeagueInvitationStatus::REVOKED,
            'revoked_at' => now(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LeagueInvitationStatus::PENDING,
            'expires_at' => now()->subMinute(),
        ]);
    }
}
