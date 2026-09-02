<?php

namespace Database\Factories\Team;

use App\Domain\Team\Enums\TeamStatus;
use App\Models\Season\SeasonParticipation;
use App\Models\Team\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Team>
 */
class TeamFactory extends Factory
{
    protected $model = Team::class;

    public function definition(): array
    {
        return [
            'season_participation_id' => SeasonParticipation::factory(),
            'name' => fake()->company(),
            'short_name' => fake()->unique()->lexify('???'),
            'logo_path' => null,
            'status' => TeamStatus::ACTIVE,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TeamStatus::INACTIVE,
        ]);
    }
}
