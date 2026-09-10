<?php

namespace App\Events\Auction;

use App\Models\Auction\AuctionNomination;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AuctionNominationStarted implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public AuctionNomination $nomination
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(
                'auction.' . $this->nomination->auction->ulid
            ),
        ];
    }

    public function broadcastAs(): string
    {
        return 'auction.nomination.started';
    }

    public function broadcastWith(): array
    {
        return [
            'auction_ulid' => $this->nomination->auction->ulid,
            'nomination_ulid' => $this->nomination->ulid,
            'player_season_ulid' => $this->nomination->playerSeason->ulid,
            'auction_participant_ulid' => $this->nomination->participant->ulid,
            'turn_number' => $this->nomination->turn_number,
            'opening_price' => $this->nomination->opening_price,
            'timer_started_at' => $this->nomination->timer_started_at?->toISOString(),
            'expires_at' => $this->nomination->expires_at?->toISOString(),
        ];
    }
}
