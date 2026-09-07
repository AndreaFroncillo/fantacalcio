<?php

namespace Database\Factories\Auction;

use App\Domain\Auction\Enums\AuctionStatus;
use App\Models\Auction\Auction;
use App\Models\Market\MarketSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Auction>
 */
class AuctionFactory extends Factory
{
    protected $model = Auction::class;

    public function definition(): array
    {
        return [
            'market_session_id' => MarketSession::factory(),
            'status' => AuctionStatus::SCHEDULED,
            'base_timer_seconds' => 60,
            'bid_extension_seconds' => 10,
            'started_at' => null,
            'completed_at' => null,
        ];
    }

    public function live(): static
    {
        return $this->state(fn () => [
            'status' => AuctionStatus::LIVE,
            'started_at' => now(),
            'completed_at' => null,
        ]);
    }

    public function paused(): static
    {
        return $this->state(fn () => [
            'status' => AuctionStatus::PAUSED,
            'started_at' => now(),
            'completed_at' => null,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => AuctionStatus::COMPLETED,
            'started_at' => now()->subHour(),
            'completed_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => [
            'status' => AuctionStatus::CANCELLED,
        ]);
    }
}
