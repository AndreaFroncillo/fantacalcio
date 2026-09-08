<?php

namespace App\Domain\Auction\Actions;

use App\Models\Auction\Auction;

class AdvanceAuctionLifecycle
{
    public function __construct(
        private ResolveNextAuctionTurn $resolveNextAuctionTurn,
        private AdvanceAuctionRolePhase $advanceAuctionRolePhase,
        private CompleteAuction $completeAuction
    ) {}

    public function execute(Auction $auction): Auction
    {
        $nextTurn = $this
            ->resolveNextAuctionTurn
            ->execute($auction);

        if ($nextTurn !== null) {
            return $auction->fresh();
        }

        $nextPhase = $this
            ->advanceAuctionRolePhase
            ->execute($auction);

        if ($nextPhase !== null) {
            return $auction->fresh();
        }

        return $this->completeAuction
            ->execute($auction);
    }
}
