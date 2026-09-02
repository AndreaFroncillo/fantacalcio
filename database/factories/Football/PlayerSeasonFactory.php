<?php

namespace Database\Factories\Football;

use App\Domain\Football\Enums\PlayerRole;
use App\Domain\Football\Enums\PlayerSeasonStatus;
use App\Models\Football\FootballPlayer;
use App\Models\Football\FootballSeason;
use App\Models\Football\PlayerSeason;
use App\Models\Football\RealClub;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlayerSeason>
 */
class PlayerSeasonFactory extends Factory
{
    protected $model = PlayerSeason::class;

    public function definition(): array
    {
        return [
            'football_season_id' => FootballSeason::factory(),
            'football_player_id' => FootballPlayer::factory(),
            'real_club_id' => RealClub::factory(),
            'role' => fake()->randomElement(PlayerRole::cases()),
            'status' => PlayerSeasonStatus::ACTIVE,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PlayerSeasonStatus::INACTIVE,
        ]);
    }
}
