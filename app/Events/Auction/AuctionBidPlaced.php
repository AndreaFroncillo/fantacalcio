<?php

namespace App\Events\Auction;

use App\Models\Auction\AuctionBid;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AuctionBidPlaced implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public AuctionBid $bid
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(
                'auction.' . $this->bid->nomination->auction->ulid
            ),
        ];
    }

    public function broadcastAs(): string
    {
        return 'auction.bid.placed';
    }

    public function broadcastWith(): array
    {
        return [
            'auction_ulid' => $this->bid->nomination->auction->ulid,
            'nomination_ulid' => $this->bid->nomination->ulid,
            'bid_ulid' => $this->bid->ulid,
            'auction_participant_ulid' => $this->bid->participant->ulid,
            'amount' => $this->bid->amount,
            'sequence_number' => $this->bid->sequence_number,
            'placed_at' => $this->bid->placed_at?->toISOString(),
            'expires_at' => $this->bid->nomination->expires_at?->toISOString(),
        ];
    }
}
