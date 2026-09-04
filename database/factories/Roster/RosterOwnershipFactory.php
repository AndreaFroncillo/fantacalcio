<?php

namespace Database\Factories\Roster;

use App\Models\Football\PlayerSeason;
use App\Models\Roster\RosterOwnership;
use App\Models\Season\LeagueSeason;
use App\Models\Team\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RosterOwnership>
 */
class RosterOwnershipFactory extends Factory
{
    protected $model = RosterOwnership::class;

    public function definition(): array
    {
        return [
            'league_season_id' => LeagueSeason::factory(),
            'team_id' => Team::factory(),
            'player_season_id' => PlayerSeason::factory(),
            'acquisition_value' => fake()->numberBetween(1, 500),
            'acquired_at' => now(),
            'released_at' => null,
        ];
    }

    public function released(): static
    {
        return $this->state(fn (array $attributes) => [
            'released_at' => now(),
        ]);
    }
}
