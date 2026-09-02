<?php

namespace Database\Factories\Season;

use App\Domain\Season\Enums\LeagueSeasonStatus;
use App\Models\League\League;
use App\Models\Season\LeagueSeason;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeagueSeason>
 */
class LeagueSeasonFactory extends Factory
{
    public function definition(): array
    {
        $startYear = fake()->numberBetween(2020, 2035);

        return [
            'league_id' => League::factory(),
            'start_year' => $startYear,
            'end_year' => $startYear + 1,
            'status' => LeagueSeasonStatus::DRAFT,
            'currency' => 'EUR',
            'max_teams' => 10,
            'initial_credits' => 500,
            'starts_at' => null,
            'ends_at' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LeagueSeasonStatus::ACTIVE,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LeagueSeasonStatus::COMPLETED,
        ]);
    }
}
