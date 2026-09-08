<?php

namespace App\Domain\Auction\Actions;

use App\Domain\Auction\Enums\AuctionRolePhaseStatus;
use App\Models\Auction\Auction;
use RuntimeException;

class HasEligibleAuctionParticipant
{
    public function execute(Auction $auction): bool
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

        $leagueSeason = $auction
            ->marketSession
            ->leagueSeason;

        $maxRosterSize = $leagueSeason
            ->rosterRules()
            ->sum('max_players');

        $rosterRule = $leagueSeason
            ->rosterRules()
            ->where('role', $activePhase->role->value)
            ->first();

        if (! $rosterRule) {
            throw new RuntimeException(
                'Roster rule for active auction role is missing.'
            );
        }

        foreach ($participants as $participant) {
            $creditAccount = $participant
                ->team
                ->creditAccount;

            if (! $creditAccount) {
                throw new RuntimeException(
                    'Auction participant team has no credit account.'
                );
            }

            $activeRosterOwnerships = $participant
                ->team
                ->rosterOwnerships()
                ->whereNull('released_at')
                ->count();

            if (
                $maxRosterSize > 0
                && $activeRosterOwnerships >= $maxRosterSize
            ) {
                continue;
            }

            $activeRoleOwnerships = $participant
                ->team
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
                continue;
            }

            if ($creditAccount->current_balance === 0) {
                continue;
            }

            return true;
        }

        return false;
    }
}
