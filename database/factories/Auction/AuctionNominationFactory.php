<?php

namespace Database\Factories\Auction;

use App\Domain\Auction\Enums\AuctionNominationCloseReason;
use App\Domain\Auction\Enums\AuctionNominationStatus;
use App\Models\Auction\Auction;
use App\Models\Auction\AuctionNomination;
use App\Models\Auction\AuctionParticipant;
use App\Models\Auction\AuctionRolePhase;
use App\Models\Football\PlayerSeason;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuctionNomination>
 */
class AuctionNominationFactory extends Factory
{
    protected $model = AuctionNomination::class;

    public function definition(): array
    {
        $auction = Auction::factory();

        $timerStartedAt = now();
        $expiresAt = $timerStartedAt->copy()->addSeconds(60);

        return [
            'auction_id' => $auction,
            'auction_role_phase_id' => AuctionRolePhase::factory()->for($auction),
            'auction_participant_id' => AuctionParticipant::factory()->for($auction),
            'player_season_id' => PlayerSeason::factory(),
            'turn_number' => 1,
            'status' => AuctionNominationStatus::ACTIVE,
            'opening_price' => 1,
            'timer_started_at' => $timerStartedAt,
            'expires_at' => $expiresAt,
            'closed_at' => null,
            'closed_by_user_id' => null,
            'close_reason' => null,
        ];
    }

    public function completedByTimer(): static
    {
        return $this->state(fn () => [
            'status' => AuctionNominationStatus::COMPLETED,
            'closed_at' => now(),
            'closed_by_user_id' => null,
            'close_reason' => AuctionNominationCloseReason::TIMER_EXPIRED,
        ]);
    }

    public function confirmedByPresident(): static
    {
        return $this->state(fn () => [
            'status' => AuctionNominationStatus::COMPLETED,
            'closed_at' => now(),
            'closed_by_user_id' => User::factory(),
            'close_reason' => AuctionNominationCloseReason::PRESIDENT_CONFIRMED,
        ]);
    }

    public function rejectedByPresident(): static
    {
        return $this->state(fn () => [
            'status' => AuctionNominationStatus::REJECTED,
            'closed_at' => now(),
            'closed_by_user_id' => User::factory(),
            'close_reason' => AuctionNominationCloseReason::PRESIDENT_REJECTED,
        ]);
    }
}
