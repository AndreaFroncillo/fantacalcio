<?php

namespace Database\Factories\Auction;

use App\Domain\Auction\Enums\AuctionRolePhaseStatus;
use App\Domain\Football\Enums\PlayerRole;
use App\Models\Auction\Auction;
use App\Models\Auction\AuctionRolePhase;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuctionRolePhase>
 */
class AuctionRolePhaseFactory extends Factory
{
    protected $model = AuctionRolePhase::class;

    public function definition(): array
    {
        return [
            'auction_id' => Auction::factory(),
            'role' => PlayerRole::GOALKEEPER,
            'position' => 1,
            'status' => AuctionRolePhaseStatus::PENDING,
            'started_at' => null,
            'completed_at' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'status' => AuctionRolePhaseStatus::ACTIVE,
            'started_at' => now(),
            'completed_at' => null,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => AuctionRolePhaseStatus::COMPLETED,
            'started_at' => now()->subHour(),
            'completed_at' => now(),
        ]);
    }
}
