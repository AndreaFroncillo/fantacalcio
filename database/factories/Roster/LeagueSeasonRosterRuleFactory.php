<?php

namespace Database\Factories\Roster;

use App\Domain\Football\Enums\PlayerRole;
use App\Models\Roster\LeagueSeasonRosterRule;
use App\Models\Season\LeagueSeason;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeagueSeasonRosterRule>
 */
class LeagueSeasonRosterRuleFactory extends Factory
{
    protected $model = LeagueSeasonRosterRule::class;

    public function definition(): array
    {
        return [
            'league_season_id' => LeagueSeason::factory(),
            'role' => fake()->randomElement(PlayerRole::cases()),
            'max_players' => fake()->numberBetween(1, 10),
        ];
    }
}
