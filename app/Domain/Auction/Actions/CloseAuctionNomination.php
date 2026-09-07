<?php

namespace App\Domain\Auction\Actions;

use App\Domain\Auction\Enums\AuctionNominationCloseReason;
use App\Domain\Auction\Enums\AuctionNominationStatus;
use App\Domain\Auction\Enums\AuctionStatus;
use App\Models\Auction\AuctionNomination;
use App\Models\League\LeagueMembership;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CloseAuctionNomination
{
    public function execute(
        AuctionNomination $nomination,
        AuctionNominationCloseReason $reason,
        ?User $actor = null
    ): AuctionNomination {
        return DB::transaction(function () use ($nomination, $reason, $actor) {
            $nomination = AuctionNomination::query()
                ->whereKey($nomination->id)
                ->lockForUpdate()
                ->firstOrFail();

            $auction = $nomination->auction()
                ->lockForUpdate()
                ->firstOrFail();

            if ($auction->status !== AuctionStatus::LIVE) {
                throw new RuntimeException(
                    'Auction is not live.'
                );
            }

            if ($nomination->status !== AuctionNominationStatus::ACTIVE) {
                throw new RuntimeException(
                    'Auction nomination is not active.'
                );
            }

            if ($reason === AuctionNominationCloseReason::TIMER_EXPIRED) {
                if ($actor) {
                    throw new RuntimeException(
                        'Timer expiration cannot have an actor.'
                    );
                }

                if (
                    ! $nomination->expires_at ||
                    $nomination->expires_at->isAfter(now())
                ) {
                    throw new RuntimeException(
                        'Auction nomination timer has not expired.'
                    );
                }

                $nomination->update([
                    'status' => AuctionNominationStatus::COMPLETED,
                    'closed_at' => now(),
                    'closed_by_user_id' => null,
                    'close_reason' => AuctionNominationCloseReason::TIMER_EXPIRED,
                ]);
            }

            if ($reason === AuctionNominationCloseReason::PRESIDENT_CONFIRMED) {
                $actor = $this->ensurePresidentActor(
                    $nomination,
                    $actor
                );

                $nomination->update([
                    'status' => AuctionNominationStatus::COMPLETED,
                    'closed_at' => now(),
                    'closed_by_user_id' => $actor->id,
                    'close_reason' => AuctionNominationCloseReason::PRESIDENT_CONFIRMED,
                ]);
            }

            if ($reason === AuctionNominationCloseReason::PRESIDENT_REJECTED) {
                $actor = $this->ensurePresidentActor(
                    $nomination,
                    $actor
                );

                $nomination->update([
                    'status' => AuctionNominationStatus::REJECTED,
                    'closed_at' => now(),
                    'closed_by_user_id' => $actor->id,
                    'close_reason' => AuctionNominationCloseReason::PRESIDENT_REJECTED,
                ]);
            }

            return $nomination->refresh();
        });
    }

    private function ensurePresidentActor(
        AuctionNomination $nomination,
        ?User $actor
    ): User {
        if (! $actor) {
            throw new RuntimeException(
                'President action requires an actor.'
            );
        }

        $leagueId = $nomination->auction
            ->marketSession
            ->leagueSeason
            ->league_id;

        $isPresident = LeagueMembership::query()
            ->where('league_id', $leagueId)
            ->where('user_id', $actor->id)
            ->where('role', 'president')
            ->exists();

        if (! $isPresident) {
            throw new RuntimeException(
                'Actor is not the league president.'
            );
        }

        return $actor;
    }
}
