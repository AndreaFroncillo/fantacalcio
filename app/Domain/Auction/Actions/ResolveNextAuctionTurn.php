<?php

namespace App\Domain\Auction\Actions;

use App\Domain\Auction\Enums\AuctionRolePhaseStatus;
use App\Domain\Auction\Enums\AuctionTurnSkipReason;
use App\Models\Auction\Auction;
use App\Models\Auction\AuctionParticipant;
use RuntimeException;

class ResolveNextAuctionTurn
{
    public function execute(Auction $auction): array
    {
        $participants = $auction->participants()
            ->with('team.creditAccount')
            ->orderBy('nomination_position')
            ->get();

        if ($participants->isEmpty()) {
            throw new RuntimeException(
                'Auction has no participants.'
            );
        }

        $activePhase = $auction->rolePhases()
            ->where('status', AuctionRolePhaseStatus::ACTIVE)
            ->first();

        if (! $activePhase) {
            throw new RuntimeException(
                'Auction has no active role phase.'
            );
        }

        $turnNumber = $this->nextTurnNumber($auction);

        for ($checked = 0; $checked < $participants->count(); $checked++) {
            $position = (($turnNumber - 1) % $participants->count()) + 1;

            /** @var AuctionParticipant|null $participant */
            $participant = $participants->firstWhere(
                'nomination_position',
                $position
            );

            if (! $participant) {
                throw new RuntimeException(
                    'Auction participant order is inconsistent.'
                );
            }

            $creditAccount = $participant->team->creditAccount;

            if (! $creditAccount) {
                throw new RuntimeException(
                    'Auction participant team has no credit account.'
                );
            }

            $leagueSeason = $auction->marketSession->leagueSeason;

            $maxRosterSize = $leagueSeason->rosterRules()
                ->sum('max_players');

            $activeRosterOwnerships = $participant->team
                ->rosterOwnerships()
                ->whereNull('released_at')
                ->count();

            if ($maxRosterSize > 0 && $activeRosterOwnerships >= $maxRosterSize) {
                $auction->turnSkips()->create([
                    'auction_role_phase_id' => $activePhase->id,
                    'auction_participant_id' => $participant->id,
                    'turn_number' => $turnNumber,
                    'reason' => AuctionTurnSkipReason::ROSTER_COMPLETE,
                    'performed_by_user_id' => null,
                    'note' => null,
                ]);

                $turnNumber++;

                continue;
            }

            $rosterRule = $leagueSeason->rosterRules()
                ->where('role', $activePhase->role->value)
                ->first();

            if (! $rosterRule) {
                throw new RuntimeException(
                    'Roster rule for active auction role is missing.'
                );
            }

            $activeRoleOwnerships = $participant->team
                ->rosterOwnerships()
                ->whereNull('released_at')
                ->whereHas('playerSeason', function ($query) use ($activePhase) {
                    $query->where(
                        'role',
                        $activePhase->role->value
                    );
                })
                ->count();

            if ($activeRoleOwnerships >= $rosterRule->max_players) {
                $auction->turnSkips()->create([
                    'auction_role_phase_id' => $activePhase->id,
                    'auction_participant_id' => $participant->id,
                    'turn_number' => $turnNumber,
                    'reason' => AuctionTurnSkipReason::ROLE_COMPLETE,
                    'performed_by_user_id' => null,
                    'note' => null,
                ]);

                $turnNumber++;

                continue;
            }

            if ($creditAccount->current_balance === 0) {
                $auction->turnSkips()->create([
                    'auction_role_phase_id' => $activePhase->id,
                    'auction_participant_id' => $participant->id,
                    'turn_number' => $turnNumber,
                    'reason' => AuctionTurnSkipReason::NO_CREDITS,
                    'performed_by_user_id' => null,
                    'note' => null,
                ]);

                $turnNumber++;

                continue;
            }

            return [
                'turn_number' => $turnNumber,
                'participant' => $participant,
            ];
        }

        throw new RuntimeException(
            'Auction has no eligible participants for the current role phase.'
        );
    }

    private function nextTurnNumber(Auction $auction): int
    {
        $lastNominationTurn = $auction->nominations()
            ->max('turn_number');

        $lastSkipTurn = $auction->turnSkips()
            ->max('turn_number');

        return max(
            $lastNominationTurn ?? 0,
            $lastSkipTurn ?? 0
        ) + 1;
    }
}
