<?php

namespace App\Jobs\Auction;

use App\Domain\Auction\Actions\ExpireAuctionNomination;
use App\Domain\Auction\Enums\AuctionNominationStatus;
use App\Domain\Auction\Enums\AuctionStatus;
use App\Models\Auction\AuctionNomination;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessExpiredAuctionNominationsJob implements ShouldQueue
{
    use Queueable;

    public function handle(
        ExpireAuctionNomination $expireAuctionNomination
    ): void {
        $nominations = AuctionNomination::query()
            ->where('status', AuctionNominationStatus::ACTIVE)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->whereHas('auction', function ($query) {
                $query->where('status', AuctionStatus::LIVE);
            })
            ->orderBy('expires_at')
            ->get();

        foreach ($nominations as $nomination) {
            $expireAuctionNomination->execute($nomination);
        }
    }
}
