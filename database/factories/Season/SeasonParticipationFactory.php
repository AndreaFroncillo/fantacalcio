<?php

namespace Database\Factories\Season;

use App\Domain\Season\Enums\SeasonParticipationStatus;
use App\Models\League\LeagueMembership;
use App\Models\Season\LeagueSeason;
use App\Models\Season\SeasonParticipation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SeasonParticipation>
 */
class SeasonParticipationFactory extends Factory
{
    protected $model = SeasonParticipation::class;

    public function definition(): array
    {
        return [
            'league_season_id' => LeagueSeason::factory(),
            'league_membership_id' => LeagueMembership::factory(),
            'status' => SeasonParticipationStatus::ACTIVE,
            'joined_at' => now(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SeasonParticipationStatus::INACTIVE,
        ]);
    }
}
