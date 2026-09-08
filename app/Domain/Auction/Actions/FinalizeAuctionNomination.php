<?php

namespace App\Domain\Auction\Actions;

use App\Domain\Auction\Enums\AuctionNominationCloseReason;
use App\Domain\Auction\Enums\AuctionNominationStatus;
use App\Models\Auction\AuctionNomination;
use App\Models\Roster\RosterOwnership;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class FinalizeAuctionNomination
{
    public function __construct(
        private CloseAuctionNomination $closeAuctionNomination,
        private AcquireAuctionPlayer $acquireAuctionPlayer
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
            $nomination = $this->closeAuctionNomination->execute(
                $nomination,
                $reason,
                $actor
            );

            if ($nomination->status !== AuctionNominationStatus::COMPLETED) {
                return null;
            }

            return $this->acquireAuctionPlayer->execute(
                $nomination
            );
        });
    }
}
