<?php

namespace App\Domain\Auction\Actions;

use App\Domain\Auction\Enums\AuctionNominationCloseReason;
use App\Models\Auction\AuctionNomination;
use App\Models\Roster\RosterOwnership;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class FinalizeAndProgressAuction
{
    public function __construct(
        private FinalizeAuctionNomination $finalizeAuctionNomination,
        private AdvanceAuctionLifecycle $advanceAuctionLifecycle
    ) {}

    public function execute(
        AuctionNomination $nomination,
        AuctionNominationCloseReason $reason,
        ?User $actor = null
    ): ?RosterOwnership {
        return DB::transaction(function () use (
            $nomination,
            $reason,
            $actor
        ) {
            $ownership = $this
                ->finalizeAuctionNomination
                ->execute(
                    $nomination,
                    $reason,
                    $actor
                );

            $this
                ->advanceAuctionLifecycle
                ->execute(
                    $nomination->auction
                );

            return $ownership;
        });
    }
}
