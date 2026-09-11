<?php

namespace App\Events\Auction;

use App\Models\Auction\Auction;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AuctionStarted implements ShouldBroadcast, ShouldDispatchAfterCommit
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
        return 'auction.started';
    }

    public function broadcastWith(): array
    {
        $activePhase = $this->auction
            ->rolePhases
            ->firstWhere('status', \App\Domain\Auction\Enums\AuctionRolePhaseStatus::ACTIVE);

        return [
            'auction_ulid' => $this->auction->ulid,
            'status' => $this->auction->status->value,
            'started_at' => $this->auction->started_at?->toISOString(),
            'active_role_phase_ulid' => $activePhase?->ulid,
            'active_role' => $activePhase?->role->value,
        ];
    }
}
