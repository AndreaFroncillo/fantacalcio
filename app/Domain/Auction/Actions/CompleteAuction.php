<?php

namespace App\Domain\Auction\Actions;

use App\Domain\Auction\Enums\AuctionNominationStatus;
use App\Domain\Auction\Enums\AuctionRolePhaseStatus;
use App\Domain\Auction\Enums\AuctionStatus;
use App\Events\Auction\AuctionCompleted;
use App\Models\Auction\Auction;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CompleteAuction
{
    public function execute(Auction $auction): Auction
    {
        return DB::transaction(function () use ($auction) {
            $auction = Auction::query()
                ->whereKey($auction->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($auction->status !== AuctionStatus::LIVE) {
                throw new RuntimeException(
                    'Auction is not live.'
                );
            }

            $hasActiveNomination = $auction
                ->nominations()
                ->where('status', AuctionNominationStatus::ACTIVE)
                ->exists();

            if ($hasActiveNomination) {
                throw new RuntimeException(
                    'Auction has an active nomination.'
                );
            }

            $hasIncompleteRolePhases = $auction
                ->rolePhases()
                ->where('status', '!=', AuctionRolePhaseStatus::COMPLETED)
                ->exists();

            if ($hasIncompleteRolePhases) {
                throw new RuntimeException(
                    'Auction has incomplete role phases.'
                );
            }

            $auction->update([
                'status' => AuctionStatus::COMPLETED,
                'completed_at' => now(),
            ]);

            $auction = $auction->refresh();

            AuctionCompleted::dispatch($auction);

            return $auction;
        });
    }
}
