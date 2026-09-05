<?php

namespace Database\Factories\Market;

use App\Domain\Market\Enums\MarketSessionStatus;
use App\Models\Market\MarketSession;
use App\Models\Season\LeagueSeason;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MarketSession>
 */
class MarketSessionFactory extends Factory
{
    protected $model = MarketSession::class;

    public function definition(): array
    {
        return [
            'league_season_id' => LeagueSeason::factory(),
            'name' => fake()->words(3, true),
            'status' => MarketSessionStatus::SCHEDULED,
            'starts_at' => null,
            'ends_at' => null,
        ];
    }

    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => MarketSessionStatus::OPEN,
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => MarketSessionStatus::CLOSED,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => MarketSessionStatus::CANCELLED,
        ]);
    }
}
