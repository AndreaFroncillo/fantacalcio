<?php

namespace App\Domain\Auction\Actions;

use App\Domain\Auction\Enums\AuctionNominationCloseReason;
use App\Models\Auction\AuctionNomination;
use App\Models\Roster\RosterOwnership;

class ExpireAuctionNomination
{
    public function __construct(
        private FinalizeAndProgressAuction $finalizeAndProgressAuction
    ) {}

    public function execute(
        AuctionNomination $nomination
    ): ?RosterOwnership {
        return $this
            ->finalizeAndProgressAuction
            ->execute(
                $nomination,
                AuctionNominationCloseReason::TIMER_EXPIRED
            );
    }
}
