<?php

namespace Database\Factories\Market;

use App\Models\Market\MarketSession;
use App\Models\Market\PlayerRelease;
use App\Models\Roster\RosterOwnership;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlayerRelease>
 */
class PlayerReleaseFactory extends Factory
{
    protected $model = PlayerRelease::class;

    public function definition(): array
    {
        return [
            'market_session_id' => MarketSession::factory(),
            'roster_ownership_id' => RosterOwnership::factory(),
            'team_id' => fn (array $attributes) => RosterOwnership::find(
                $attributes['roster_ownership_id']
            )->team_id,
            'player_season_id' => fn (array $attributes) => RosterOwnership::find(
                $attributes['roster_ownership_id']
            )->player_season_id,
            'release_value' => fake()->numberBetween(0, 500),
            'released_at' => now(),
        ];
    }
}
