<?php

namespace App\Events\Auction;

use App\Models\Auction\AuctionRolePhase;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AuctionRolePhaseAdvanced implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public AuctionRolePhase $previousPhase,
        public AuctionRolePhase $nextPhase
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(
                'auction.' . $this->nextPhase->auction->ulid
            ),
        ];
    }

    public function broadcastAs(): string
    {
        return 'auction.role-phase.advanced';
    }

    public function broadcastWith(): array
    {
        return [
            'auction_ulid' => $this->nextPhase->auction->ulid,
            'previous_phase_ulid' => $this->previousPhase->ulid,
            'previous_role' => $this->previousPhase->role->value,
            'next_phase_ulid' => $this->nextPhase->ulid,
            'next_role' => $this->nextPhase->role->value,
            'started_at' => $this->nextPhase->started_at?->toISOString(),
        ];
    }
}
