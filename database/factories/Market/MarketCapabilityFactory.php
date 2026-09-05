<?php

namespace Database\Factories\Market;

use App\Domain\Market\Enums\MarketCapabilityType;
use App\Models\Market\MarketCapability;
use App\Models\Market\MarketSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MarketCapability>
 */
class MarketCapabilityFactory extends Factory
{
    protected $model = MarketCapability::class;

    public function definition(): array
    {
        return [
            'market_session_id' => MarketSession::factory(),
            'type' => MarketCapabilityType::AUCTION,
            'is_enabled' => false,
        ];
    }

    public function enabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_enabled' => true,
        ]);
    }
}
