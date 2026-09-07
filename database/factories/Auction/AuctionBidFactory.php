<?php

namespace Database\Factories\Auction;

use App\Models\Auction\AuctionBid;
use App\Models\Auction\AuctionNomination;
use App\Models\Auction\AuctionParticipant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuctionBid>
 */
class AuctionBidFactory extends Factory
{
    protected $model = AuctionBid::class;

    public function definition(): array
    {
        $nomination = AuctionNomination::factory();

        return [
            'auction_nomination_id' => $nomination,
            'auction_participant_id' => AuctionParticipant::factory(),
            'amount' => 1,
            'sequence_number' => 1,
            'placed_at' => now(),
        ];
    }
}
