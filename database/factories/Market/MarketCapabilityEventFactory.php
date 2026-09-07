<?php

namespace Database\Factories\Market;

use App\Domain\Market\Enums\MarketCapabilityEventType;
use App\Models\Market\MarketCapability;
use App\Models\Market\MarketCapabilityEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MarketCapabilityEvent>
 */
class MarketCapabilityEventFactory extends Factory
{
    protected $model = MarketCapabilityEvent::class;

    public function definition(): array
    {
        return [
            'market_capability_id' => MarketCapability::factory(),
            'type' => MarketCapabilityEventType::ENABLED,
            'performed_by_user_id' => User::factory(),
            'reason' => null,
        ];
    }

    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => MarketCapabilityEventType::DISABLED,
        ]);
    }

    public function system(): static
    {
        return $this->state(fn (array $attributes) => [
            'performed_by_user_id' => null,
        ]);
    }
}
