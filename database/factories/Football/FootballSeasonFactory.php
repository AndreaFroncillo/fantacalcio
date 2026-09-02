<?php

namespace Database\Factories\Football;

use App\Domain\Football\Enums\FootballSeasonStatus;
use App\Models\Football\FootballSeason;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FootballSeason>
 */
class FootballSeasonFactory extends Factory
{
    protected $model = FootballSeason::class;

    public function definition(): array
    {
        $startYear = fake()->numberBetween(2020, 2035);

        return [
            'name' => sprintf(
                'Serie A %d/%d',
                $startYear,
                $startYear + 1
            ),
            'start_year' => $startYear,
            'end_year' => $startYear + 1,
            'status' => FootballSeasonStatus::DRAFT,
            'starts_at' => null,
            'ends_at' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => FootballSeasonStatus::ACTIVE,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => FootballSeasonStatus::COMPLETED,
        ]);
    }
}
