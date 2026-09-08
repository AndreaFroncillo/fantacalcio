<?php

namespace App\Domain\Auction\Actions;

use App\Domain\Auction\Enums\AuctionNominationStatus;
use App\Domain\Auction\Enums\AuctionRolePhaseStatus;
use App\Domain\Auction\Enums\AuctionStatus;
use App\Models\Auction\Auction;
use App\Models\Auction\AuctionRolePhase;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AdvanceAuctionRolePhase
{
    public function __construct(
        private HasEligibleAuctionParticipant $hasEligibleAuctionParticipant
    ) {}

    public function execute(
        Auction $auction
    ): ?AuctionRolePhase {
        $result = DB::transaction(function () use ($auction) {
            $auction = Auction::query()
                ->whereKey($auction->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($auction->status !== AuctionStatus::LIVE) {
                throw new RuntimeException(
                    'Auction is not live.'
                );
            }

            $activePhase = $auction
                ->rolePhases()
                ->where('status', AuctionRolePhaseStatus::ACTIVE)
                ->lockForUpdate()
                ->first();

            if (! $activePhase) {
                throw new RuntimeException(
                    'Auction has no active role phase.'
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

            $hasEligibleParticipant = $this
                ->hasEligibleAuctionParticipant
                ->execute($auction);

            if ($hasEligibleParticipant) {
                return [
                    'has_eligible_participant' => true,
                    'next_phase' => null,
                ];
            }

            $activePhase->update([
                'status' => AuctionRolePhaseStatus::COMPLETED,
                'completed_at' => now(),
            ]);

            $nextPhase = $auction
                ->rolePhases()
                ->where('position', '>', $activePhase->position)
                ->where('status', AuctionRolePhaseStatus::PENDING)
                ->orderBy('position')
                ->lockForUpdate()
                ->first();

            if (! $nextPhase) {
                return [
                    'has_eligible_participant' => false,
                    'next_phase' => null,
                ];
            }

            $nextPhase->update([
                'status' => AuctionRolePhaseStatus::ACTIVE,
                'started_at' => now(),
                'completed_at' => null,
            ]);

            return [
                'has_eligible_participant' => false,
                'next_phase' => $nextPhase->refresh(),
            ];
        });

        if ($result['has_eligible_participant']) {
            throw new RuntimeException(
                'Auction role phase still has eligible participants.'
            );
        }

        return $result['next_phase'];
    }
}
