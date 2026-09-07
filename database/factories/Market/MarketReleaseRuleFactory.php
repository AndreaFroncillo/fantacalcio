<?php

namespace Database\Factories\Market;

use App\Models\Market\MarketReleaseRule;
use App\Models\Market\MarketSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MarketReleaseRule>
 */
class MarketReleaseRuleFactory extends Factory
{
    protected $model = MarketReleaseRule::class;

    public function definition(): array
    {
        return [
            'market_session_id' => MarketSession::factory(),
            'max_releases_per_team' => fake()->numberBetween(1, 10),
        ];
    }

    public function unlimited(): static
    {
        return $this->state(fn (array $attributes) => [
            'max_releases_per_team' => null,
        ]);
    }
}
