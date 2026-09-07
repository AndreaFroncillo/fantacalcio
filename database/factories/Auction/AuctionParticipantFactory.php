<?php

namespace Database\Factories\Auction;

use App\Models\Auction\Auction;
use App\Models\Auction\AuctionParticipant;
use App\Models\Team\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuctionParticipant>
 */
class AuctionParticipantFactory extends Factory
{
    protected $model = AuctionParticipant::class;

    public function definition(): array
    {
        return [
            'auction_id' => Auction::factory(),
            'team_id' => Team::factory(),
            'nomination_position' => 1,
        ];
    }
}
