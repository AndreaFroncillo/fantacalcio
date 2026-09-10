<?php

namespace App\Domain\Auction\Actions;

use App\Domain\Auction\Enums\AuctionNominationStatus;
use App\Domain\Auction\Enums\AuctionRolePhaseStatus;
use App\Domain\Auction\Enums\AuctionStatus;
use App\Models\Auction\Auction;
use App\Models\Auction\AuctionNomination;
use App\Models\Football\PlayerSeason;
use App\Models\Roster\RosterOwnership;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StartAuctionNomination
{
    public function __construct(
        private readonly ResolveNextAuctionTurn $resolveNextAuctionTurn
    ) {}

    public function execute(
        Auction $auction,
        PlayerSeason $playerSeason,
        User $user
    ): ?AuctionNomination {
        return DB::transaction(function () use ($auction, $playerSeason, $user) {
            $auction = Auction::query()
                ->lockForUpdate()
                ->findOrFail($auction->id);

            if ($auction->status !== AuctionStatus::LIVE) {
                throw new RuntimeException(
                    'Auction is not live.'
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

            $playerAlreadyInActiveNomination = $auction->nominations()
                ->where('status', AuctionNominationStatus::ACTIVE)
                ->where('player_season_id', $playerSeason->id)
                ->exists();

            if ($playerAlreadyInActiveNomination) {
                throw new RuntimeException(
                    'Player is already in an active nomination.'
                );
            }

            $activeNominationExists = $auction->nominations()
                ->where('status', AuctionNominationStatus::ACTIVE)
                ->exists();

            if ($activeNominationExists) {
                throw new RuntimeException(
                    'Auction already has an active nomination.'
                );
            }

            if ($playerSeason->role !== $activePhase->role) {
                throw new RuntimeException(
                    'Player role does not match the active auction role phase.'
                );
            }

            $leagueSeason = $auction->marketSession->leagueSeason;

            $footballSeason = $playerSeason->footballSeason;

            if (
                $footballSeason->start_year !== $leagueSeason->start_year ||
                $footballSeason->end_year !== $leagueSeason->end_year
            ) {
                throw new RuntimeException(
                    'Player does not belong to the football season of this league season.'
                );
            }

            $alreadyOwned = RosterOwnership::query()
                ->where('league_season_id', $leagueSeason->id)
                ->where('player_season_id', $playerSeason->id)
                ->whereNull('released_at')
                ->exists();

            if ($alreadyOwned) {
                throw new RuntimeException(
                    'Player is already actively owned in this league season.'
                );
            }

            $turn = $this->resolveNextAuctionTurn->execute($auction);

            if ($turn === null) {
                return null;
            }

            $turnUserId = $turn['participant']
                ->team
                ->seasonParticipation
                ->leagueMembership
                ->user_id;

            if ($turnUserId !== $user->id) {
                throw new RuntimeException(
                    'It is not this user\'s auction turn.'
                );
            }

            $now = now();

            return $auction->nominations()->create([
                'auction_role_phase_id' => $activePhase->id,
                'auction_participant_id' => $turn['participant']->id,
                'player_season_id' => $playerSeason->id,
                'turn_number' => $turn['turn_number'],
                'status' => AuctionNominationStatus::ACTIVE,
                'opening_price' => 1,
                'timer_started_at' => $now,
                'expires_at' => $now->copy()->addSeconds(
                    $auction->base_timer_seconds
                ),
                'closed_at' => null,
                'closed_by_user_id' => null,
                'close_reason' => null,
            ]);
        });
    }
}
