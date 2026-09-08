<?php

namespace App\Domain\Auction\Actions;

use App\Domain\Auction\Enums\AuctionNominationStatus;
use App\Domain\Credit\Enums\CreditTransactionType;
use App\Models\Auction\AuctionNomination;
use App\Models\Credit\CreditTransaction;
use App\Models\Roster\LeagueSeasonRosterRule;
use App\Models\Roster\RosterOwnership;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AcquireAuctionPlayer
{
    public function execute(
        AuctionNomination $nomination
    ): ?RosterOwnership {
        return DB::transaction(function () use ($nomination) {
            $nomination = AuctionNomination::query()
                ->whereKey($nomination->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($nomination->status !== AuctionNominationStatus::COMPLETED) {
                throw new RuntimeException(
                    'Auction nomination is not completed.'
                );
            }

            $winningBid = $nomination->bids()
                ->reorder()
                ->orderByDesc('sequence_number')
                ->first();

            if (! $winningBid) {
                return null;
            }

            if ($winningBid->participant->auction_id !== $nomination->auction_id) {
                throw new RuntimeException(
                    'Winning participant does not belong to the nomination auction.'
                );
            }

            $leagueSeasonId = $nomination
                ->auction
                ->marketSession
                ->league_season_id;

            $playerAlreadyOwned = RosterOwnership::query()
                ->where('league_season_id', $leagueSeasonId)
                ->where('player_season_id', $nomination->player_season_id)
                ->whereNull('released_at')
                ->exists();

            if ($playerAlreadyOwned) {
                throw new RuntimeException(
                    'Player is already owned in this league season.'
                );
            }

            $rosterRule = LeagueSeasonRosterRule::query()
                ->where('league_season_id', $leagueSeasonId)
                ->where('role', $nomination->playerSeason->role)
                ->first();

            if (! $rosterRule) {
                throw new RuntimeException(
                    'Roster rule for player role is missing.'
                );
            }

            $activeRoleOwnerships = RosterOwnership::query()
                ->where('league_season_id', $leagueSeasonId)
                ->where('team_id', $winningBid->participant->team_id)
                ->whereNull('released_at')
                ->whereHas('playerSeason', function ($query) use ($nomination) {
                    $query->where(
                        'role',
                        $nomination->playerSeason->role
                    );
                })
                ->count();

            if ($activeRoleOwnerships >= $rosterRule->max_players) {
                throw new RuntimeException(
                    'Winning team has completed the role quota.'
                );
            }

            $creditAccount = $winningBid
                ->participant
                ->team
                ->creditAccount()
                ->lockForUpdate()
                ->first();

            if (! $creditAccount) {
                throw new RuntimeException(
                    'Auction participant team has no credit account.'
                );
            }

            if ($winningBid->amount > $creditAccount->current_balance) {
                throw new RuntimeException(
                    'Winning participant has insufficient credits.'
                );
            }

            $balanceBefore = $creditAccount->current_balance;
            $balanceAfter = $balanceBefore - $winningBid->amount;

            $creditAccount->update([
                'current_balance' => $balanceAfter,
            ]);

            CreditTransaction::create([
                'team_credit_account_id' => $creditAccount->id,
                'type' => CreditTransactionType::PLAYER_ACQUISITION,
                'amount' => -$winningBid->amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'description' => null,
            ]);

            return RosterOwnership::create([
                'league_season_id' => $leagueSeasonId,
                'team_id' => $winningBid
                    ->participant
                    ->team_id,
                'player_season_id' => $nomination->player_season_id,
                'acquisition_value' => $winningBid->amount,
                'acquired_at' => now(),
                'released_at' => null,
            ]);
        });
    }
}
