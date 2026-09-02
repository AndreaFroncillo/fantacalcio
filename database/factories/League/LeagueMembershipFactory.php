<?php

namespace Database\Factories\League;

use App\Domain\League\Enums\LeagueMembershipRole;
use App\Domain\League\Enums\LeagueMembershipStatus;
use App\Models\League\League;
use App\Models\League\LeagueMembership;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeagueMembership>
 */
class LeagueMembershipFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'league_id' => League::factory(),
            'user_id' => User::factory(),
            'role' => LeagueMembershipRole::MEMBER,
            'status' => LeagueMembershipStatus::ACTIVE,
            'joined_at' => now(),
        ];
    }

    public function president(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => LeagueMembershipRole::PRESIDENT,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LeagueMembershipStatus::INACTIVE,
        ]);
    }
}
