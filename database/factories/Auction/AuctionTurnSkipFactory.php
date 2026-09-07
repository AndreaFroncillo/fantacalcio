<?php

namespace Database\Factories\Auction;

use App\Domain\Auction\Enums\AuctionTurnSkipReason;
use App\Models\Auction\Auction;
use App\Models\Auction\AuctionParticipant;
use App\Models\Auction\AuctionRolePhase;
use App\Models\Auction\AuctionTurnSkip;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuctionTurnSkip>
 */
class AuctionTurnSkipFactory extends Factory
{
    protected $model = AuctionTurnSkip::class;

    public function definition(): array
    {
        $auction = Auction::factory();

        return [
            'auction_id' => $auction,
            'auction_role_phase_id' => AuctionRolePhase::factory()
                ->for($auction),
            'auction_participant_id' => AuctionParticipant::factory()
                ->for($auction),
            'turn_number' => 1,
            'reason' => AuctionTurnSkipReason::ROLE_COMPLETE,
            'performed_by_user_id' => null,
            'note' => null,
        ];
    }
}
