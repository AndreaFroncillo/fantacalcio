<?php

namespace Tests\Feature\Auction;

use App\Domain\Auction\Actions\FinalizeAndProgressAuction;
use App\Domain\Auction\Enums\AuctionNominationCloseReason;
use App\Domain\Auction\Enums\AuctionNominationStatus;
use App\Domain\Auction\Enums\AuctionRolePhaseStatus;
use App\Domain\Auction\Enums\AuctionStatus;
use App\Domain\Football\Enums\PlayerRole;
use App\Models\Auction\Auction;
use App\Models\Auction\AuctionBid;
use App\Models\Auction\AuctionNomination;
use App\Models\Auction\AuctionParticipant;
use App\Models\Auction\AuctionRolePhase;
use App\Models\Credit\TeamCreditAccount;
use App\Models\Football\FootballSeason;
use App\Models\Football\PlayerSeason;
use App\Models\Roster\LeagueSeasonRosterRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinalizeAndProgressAuctionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_finalizes_nomination_and_keeps_current_phase_active_when_next_turn_exists(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        $rolePhase = AuctionRolePhase::factory()
            ->active()
            ->create([
                'auction_id' => $auction->id,
                'role' => PlayerRole::GOALKEEPER,
                'position' => 1,
            ]);

        $participant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'nomination_position' => 1,
        ]);

        $creditAccount = TeamCreditAccount::factory()->create([
            'team_id' => $participant->team_id,
            'current_balance' => 100,
        ]);

        LeagueSeasonRosterRule::factory()->create([
            'league_season_id' => $auction
                ->marketSession
                ->league_season_id,
            'role' => PlayerRole::GOALKEEPER,
            'max_players' => 3,
        ]);

        $footballSeason = FootballSeason::factory()->create([
            'start_year' => $auction->marketSession->leagueSeason->start_year,
            'end_year' => $auction->marketSession->leagueSeason->end_year,
        ]);

        $playerSeason = PlayerSeason::factory()->create([
            'football_season_id' => $footballSeason->id,
            'role' => PlayerRole::GOALKEEPER,
        ]);

        $nomination = AuctionNomination::factory()
            ->create([
                'auction_id' => $auction->id,
                'auction_role_phase_id' => $rolePhase->id,
                'auction_participant_id' => $participant->id,
                'player_season_id' => $playerSeason->id,
                'status' => AuctionNominationStatus::ACTIVE,
                'turn_number' => 1,
                'opening_price' => 1,
                'timer_started_at' => now()->subMinutes(2),
                'expires_at' => now()->subMinute(),
            ]);

        AuctionBid::factory()->create([
            'auction_nomination_id' => $nomination->id,
            'auction_participant_id' => $participant->id,
            'amount' => 10,
            'sequence_number' => 1,
        ]);

        $ownership = app(FinalizeAndProgressAuction::class)->execute(
            $nomination,
            AuctionNominationCloseReason::TIMER_EXPIRED
        );

        $this->assertNotNull($ownership);

        $this->assertSame(
            AuctionNominationStatus::COMPLETED,
            $nomination->fresh()->status
        );

        $this->assertSame(
            90,
            $creditAccount->fresh()->current_balance
        );

        $this->assertSame(
            AuctionRolePhaseStatus::ACTIVE,
            $rolePhase->fresh()->status
        );

        $this->assertSame(
            AuctionStatus::LIVE,
            $auction->fresh()->status
        );

        $this->assertDatabaseHas('roster_ownerships', [
            'league_season_id' => $auction->marketSession->league_season_id,
            'team_id' => $participant->team_id,
            'player_season_id' => $playerSeason->id,
            'acquisition_value' => 10,
        ]);
    }

    public function test_it_finalizes_nomination_and_advances_to_next_role_phase_when_no_participant_is_eligible(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        $goalkeeperPhase = AuctionRolePhase::factory()
            ->active()
            ->create([
                'auction_id' => $auction->id,
                'role' => PlayerRole::GOALKEEPER,
                'position' => 1,
            ]);

        $defenderPhase = AuctionRolePhase::factory()->create([
            'auction_id' => $auction->id,
            'role' => PlayerRole::DEFENDER,
            'position' => 2,
            'status' => AuctionRolePhaseStatus::PENDING,
        ]);

        $participant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'nomination_position' => 1,
        ]);

        $creditAccount = TeamCreditAccount::factory()->create([
            'team_id' => $participant->team_id,
            'current_balance' => 10,
        ]);

        LeagueSeasonRosterRule::factory()->create([
            'league_season_id' => $auction
                ->marketSession
                ->league_season_id,
            'role' => PlayerRole::GOALKEEPER,
            'max_players' => 1,
        ]);

        $footballSeason = FootballSeason::factory()->create([
            'start_year' => $auction->marketSession->leagueSeason->start_year,
            'end_year' => $auction->marketSession->leagueSeason->end_year,
        ]);

        $playerSeason = PlayerSeason::factory()->create([
            'football_season_id' => $footballSeason->id,
            'role' => PlayerRole::GOALKEEPER,
        ]);

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'auction_role_phase_id' => $goalkeeperPhase->id,
            'auction_participant_id' => $participant->id,
            'player_season_id' => $playerSeason->id,
            'status' => AuctionNominationStatus::ACTIVE,
            'turn_number' => 1,
            'opening_price' => 1,
            'timer_started_at' => now()->subMinutes(2),
            'expires_at' => now()->subMinute(),
        ]);

        AuctionBid::factory()->create([
            'auction_nomination_id' => $nomination->id,
            'auction_participant_id' => $participant->id,
            'amount' => 10,
            'sequence_number' => 1,
        ]);

        $ownership = app(FinalizeAndProgressAuction::class)->execute(
            $nomination,
            AuctionNominationCloseReason::TIMER_EXPIRED
        );

        $this->assertNotNull($ownership);

        $this->assertSame(
            AuctionNominationStatus::COMPLETED,
            $nomination->fresh()->status
        );

        $this->assertSame(
            0,
            $creditAccount->fresh()->current_balance
        );

        $this->assertSame(
            AuctionRolePhaseStatus::COMPLETED,
            $goalkeeperPhase->fresh()->status
        );

        $this->assertNotNull(
            $goalkeeperPhase->fresh()->completed_at
        );

        $this->assertSame(
            AuctionRolePhaseStatus::ACTIVE,
            $defenderPhase->fresh()->status
        );

        $this->assertNotNull(
            $defenderPhase->fresh()->started_at
        );

        $this->assertSame(
            AuctionStatus::LIVE,
            $auction->fresh()->status
        );
    }

    public function test_it_finalizes_nomination_and_completes_auction_when_last_role_phase_has_no_eligible_participants(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        AuctionRolePhase::factory()->completed()->create([
            'auction_id' => $auction->id,
            'role' => PlayerRole::GOALKEEPER,
            'position' => 1,
        ]);

        AuctionRolePhase::factory()->completed()->create([
            'auction_id' => $auction->id,
            'role' => PlayerRole::DEFENDER,
            'position' => 2,
        ]);

        AuctionRolePhase::factory()->completed()->create([
            'auction_id' => $auction->id,
            'role' => PlayerRole::MIDFIELDER,
            'position' => 3,
        ]);

        $forwardPhase = AuctionRolePhase::factory()
            ->active()
            ->create([
                'auction_id' => $auction->id,
                'role' => PlayerRole::FORWARD,
                'position' => 4,
            ]);

        $participant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'nomination_position' => 1,
        ]);

        $creditAccount = TeamCreditAccount::factory()->create([
            'team_id' => $participant->team_id,
            'current_balance' => 10,
        ]);

        LeagueSeasonRosterRule::factory()->create([
            'league_season_id' => $auction
                ->marketSession
                ->league_season_id,
            'role' => PlayerRole::FORWARD,
            'max_players' => 1,
        ]);

        $footballSeason = FootballSeason::factory()->create([
            'start_year' => $auction->marketSession->leagueSeason->start_year,
            'end_year' => $auction->marketSession->leagueSeason->end_year,
        ]);

        $playerSeason = PlayerSeason::factory()->create([
            'football_season_id' => $footballSeason->id,
            'role' => PlayerRole::FORWARD,
        ]);

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'auction_role_phase_id' => $forwardPhase->id,
            'auction_participant_id' => $participant->id,
            'player_season_id' => $playerSeason->id,
            'status' => AuctionNominationStatus::ACTIVE,
            'turn_number' => 1,
            'opening_price' => 1,
            'timer_started_at' => now()->subMinutes(2),
            'expires_at' => now()->subMinute(),
        ]);

        AuctionBid::factory()->create([
            'auction_nomination_id' => $nomination->id,
            'auction_participant_id' => $participant->id,
            'amount' => 10,
            'sequence_number' => 1,
        ]);

        $ownership = app(FinalizeAndProgressAuction::class)->execute(
            $nomination,
            AuctionNominationCloseReason::TIMER_EXPIRED
        );

        $this->assertNotNull($ownership);

        $this->assertSame(
            AuctionNominationStatus::COMPLETED,
            $nomination->fresh()->status
        );

        $this->assertSame(
            0,
            $creditAccount->fresh()->current_balance
        );

        $this->assertSame(
            AuctionRolePhaseStatus::COMPLETED,
            $forwardPhase->fresh()->status
        );

        $this->assertNotNull(
            $forwardPhase->fresh()->completed_at
        );

        $this->assertSame(
            AuctionStatus::COMPLETED,
            $auction->fresh()->status
        );

        $this->assertNotNull(
            $auction->fresh()->completed_at
        );
    }

    public function test_it_rolls_back_finalization_when_lifecycle_progression_fails(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        $rolePhase = AuctionRolePhase::factory()
            ->active()
            ->create([
                'auction_id' => $auction->id,
                'role' => PlayerRole::GOALKEEPER,
                'position' => 1,
            ]);

        $participant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'nomination_position' => 1,
        ]);

        $secondParticipant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'nomination_position' => 2,
        ]);

        $creditAccount = TeamCreditAccount::factory()->create([
            'team_id' => $participant->team_id,
            'current_balance' => 10,
        ]);

        $footballSeason = FootballSeason::factory()->create([
            'start_year' => $auction->marketSession->leagueSeason->start_year,
            'end_year' => $auction->marketSession->leagueSeason->end_year,
        ]);

        $playerSeason = PlayerSeason::factory()->create([
            'football_season_id' => $footballSeason->id,
            'role' => PlayerRole::GOALKEEPER,
        ]);

        LeagueSeasonRosterRule::factory()->create([
            'league_season_id' => $auction
                ->marketSession
                ->league_season_id,
            'role' => PlayerRole::GOALKEEPER,
            'max_players' => 3,
        ]);

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'auction_role_phase_id' => $rolePhase->id,
            'auction_participant_id' => $participant->id,
            'player_season_id' => $playerSeason->id,
            'status' => AuctionNominationStatus::ACTIVE,
            'turn_number' => 1,
            'opening_price' => 1,
            'timer_started_at' => now()->subMinutes(2),
            'expires_at' => now()->subMinute(),
        ]);

        AuctionBid::factory()->create([
            'auction_nomination_id' => $nomination->id,
            'auction_participant_id' => $participant->id,
            'amount' => 10,
            'sequence_number' => 1,
        ]);

        try {
            app(FinalizeAndProgressAuction::class)->execute(
                $nomination,
                AuctionNominationCloseReason::TIMER_EXPIRED
            );

            $this->fail('Expected lifecycle progression to fail.');
        } catch (\RuntimeException $exception) {
            $this->assertSame(
                'Auction participant team has no credit account.',
                $exception->getMessage()
            );
        }

        $this->assertSame(
            AuctionNominationStatus::ACTIVE,
            $nomination->fresh()->status
        );

        $this->assertNull(
            $nomination->fresh()->close_reason
        );

        $this->assertNull(
            $nomination->fresh()->closed_at
        );

        $this->assertSame(
            10,
            $creditAccount->fresh()->current_balance
        );

        $this->assertDatabaseCount(
            'roster_ownerships',
            0
        );

        $this->assertDatabaseCount(
            'credit_transactions',
            0
        );

        $this->assertSame(
            AuctionRolePhaseStatus::ACTIVE,
            $rolePhase->fresh()->status
        );

        $this->assertSame(
            AuctionStatus::LIVE,
            $auction->fresh()->status
        );

        $this->assertDatabaseCount(
            'auction_turn_skips',
            0
        );
    }
}
