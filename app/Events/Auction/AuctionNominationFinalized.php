<?php

namespace App\Events\Auction;

use App\Models\Auction\AuctionNomination;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AuctionNominationFinalized implements ShouldBroadcast, ShouldDispatchAfterCommit
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
        return 'auction.nomination.finalized';
    }

    public function broadcastWith(): array
    {
        return [
            'auction_ulid' => $this->nomination->auction->ulid,
            'nomination_ulid' => $this->nomination->ulid,
            'status' => $this->nomination->status->value,
            'close_reason' => $this->nomination->close_reason->value,
            'closed_at' => $this->nomination->closed_at?->toISOString(),
        ];
    }
}
