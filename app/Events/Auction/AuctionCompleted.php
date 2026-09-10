<?php

namespace App\Events\Auction;

use App\Models\Auction\Auction;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AuctionCompleted implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Auction $auction
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(
                'auction.' . $this->auction->ulid
            ),
        ];
    }

    public function broadcastAs(): string
    {
        return 'auction.completed';
    }

    public function broadcastWith(): array
    {
        return [
            'auction_ulid' => $this->auction->ulid,
            'status' => $this->auction->status->value,
            'completed_at' => $this->auction->completed_at?->toISOString(),
        ];
    }
}
