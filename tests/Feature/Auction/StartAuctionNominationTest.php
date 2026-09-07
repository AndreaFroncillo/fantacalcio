<?php

namespace Tests\Feature\Auction;

use App\Domain\Auction\Actions\InitializeAuction;
use App\Domain\Auction\Actions\StartAuction;
use App\Domain\Auction\Actions\StartAuctionNomination;
use App\Domain\Auction\Enums\AuctionNominationCloseReason;
use App\Domain\Auction\Enums\AuctionNominationStatus;
use App\Domain\Auction\Enums\AuctionRolePhaseStatus;
use App\Domain\Auction\Enums\AuctionTurnSkipReason;
use App\Domain\Football\Enums\PlayerRole;
use App\Domain\Market\Enums\MarketCapabilityType;
use App\Domain\Market\Enums\MarketSessionStatus;
use App\Models\Auction\Auction;
use App\Models\Credit\TeamCreditAccount;
use App\Models\Football\FootballSeason;
use App\Models\Football\PlayerSeason;
use App\Models\Market\MarketCapability;
use App\Models\Roster\LeagueSeasonRosterRule;
use App\Models\Roster\RosterOwnership;
use App\Models\Season\SeasonParticipation;
use App\Models\Team\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class StartAuctionNominationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_starts_a_nomination_for_the_next_eligible_participant(): void
    {
        $auction = $this->createStartedAuction();

        $activePhase = $auction->rolePhases()
            ->where('status', AuctionRolePhaseStatus::ACTIVE)
            ->firstOrFail();

        $footballSeason = $this->createFootballSeasonForAuction($auction);

        $playerSeason = PlayerSeason::factory()->create([
            'football_season_id' => $footballSeason->id,
            'role' => PlayerRole::GOALKEEPER,
        ]);

        $nomination = app(StartAuctionNomination::class)->execute(
            $auction,
            $playerSeason
        );

        $this->assertSame(
            AuctionNominationStatus::ACTIVE,
            $nomination->status
        );

        $this->assertSame(1, $nomination->turn_number);

        $this->assertSame(
            $activePhase->id,
            $nomination->auction_role_phase_id
        );

        $this->assertSame(
            $playerSeason->id,
            $nomination->player_season_id
        );

        $this->assertSame(1, $nomination->opening_price);

        $this->assertNotNull($nomination->timer_started_at);
        $this->assertNotNull($nomination->expires_at);

        $this->assertEquals(
            $auction->base_timer_seconds,
            $nomination->timer_started_at->diffInSeconds(
                $nomination->expires_at
            )
        );
    }

    private function createStartedAuction(): Auction
    {
        $auction = Auction::factory()->create([
            'base_timer_seconds' => 60,
            'bid_extension_seconds' => 10,
        ]);

        $rosterRules = [
            PlayerRole::GOALKEEPER->value => 3,
            PlayerRole::DEFENDER->value => 8,
            PlayerRole::MIDFIELDER->value => 8,
            PlayerRole::FORWARD->value => 6,
        ];

        foreach ($rosterRules as $role => $maxPlayers) {
            LeagueSeasonRosterRule::factory()->create([
                'league_season_id' => $auction->marketSession->league_season_id,
                'role' => $role,
                'max_players' => $maxPlayers,
            ]);
        }

        $auction->marketSession->update([
            'status' => MarketSessionStatus::OPEN,
        ]);

        MarketCapability::factory()->create([
            'market_session_id' => $auction->market_session_id,
            'type' => MarketCapabilityType::AUCTION,
            'is_enabled' => true,
        ]);

        $participation = SeasonParticipation::factory()->create([
            'league_season_id' => $auction->marketSession->league_season_id,
        ]);

        $team = Team::factory()->create([
            'season_participation_id' => $participation->id,
        ]);

        TeamCreditAccount::factory()->create([
            'team_id' => $team->id,
            'initial_balance' => 500,
            'current_balance' => 500,
        ]);

        $auction = app(InitializeAuction::class)->execute($auction);

        return app(StartAuction::class)->execute($auction);
    }

    public function test_it_fails_when_auction_already_has_an_active_nomination(): void
    {
        $auction = $this->createStartedAuction();

        $activePhase = $auction->rolePhases()
            ->where('status', AuctionRolePhaseStatus::ACTIVE)
            ->firstOrFail();

        $footballSeason = $this->createFootballSeasonForAuction($auction);

        $firstPlayerSeason = PlayerSeason::factory()->create([
            'football_season_id' => $footballSeason->id,
            'role' => PlayerRole::GOALKEEPER,
        ]);

        $secondPlayerSeason = PlayerSeason::factory()->create([
            'football_season_id' => $footballSeason->id,
            'role' => PlayerRole::GOALKEEPER,
        ]);

        app(StartAuctionNomination::class)->execute(
            $auction,
            $firstPlayerSeason
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Auction already has an active nomination.'
        );

        app(StartAuctionNomination::class)->execute(
            $auction,
            $secondPlayerSeason
        );
    }

    public function test_it_fails_when_player_role_does_not_match_active_phase(): void
    {
        $auction = $this->createStartedAuction();

        $footballSeason = $this->createFootballSeasonForAuction($auction);

        $playerSeason = PlayerSeason::factory()->create([
            'football_season_id' => $footballSeason->id,
            'role' => PlayerRole::DEFENDER,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Player role does not match the active auction role phase.'
        );

        app(StartAuctionNomination::class)->execute(
            $auction,
            $playerSeason
        );
    }

    public function test_it_fails_when_auction_is_not_live(): void
    {
        $auction = Auction::factory()->create([
            'base_timer_seconds' => 60,
            'bid_extension_seconds' => 10,
        ]);

        $footballSeason = $this->createFootballSeasonForAuction($auction);

        $playerSeason = PlayerSeason::factory()->create([
            'football_season_id' => $footballSeason->id,
            'role' => PlayerRole::GOALKEEPER,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Auction is not live.'
        );

        app(StartAuctionNomination::class)->execute(
            $auction,
            $playerSeason
        );
    }

    public function test_it_returns_null_when_no_participant_is_eligible(): void
    {
        $auction = $this->createStartedAuction();

        $participant = $auction->participants()
            ->with('team.creditAccount')
            ->firstOrFail();

        $participant->team->creditAccount->update([
            'current_balance' => 0,
        ]);

        $footballSeason = $this->createFootballSeasonForAuction($auction);

        $playerSeason = PlayerSeason::factory()->create([
            'football_season_id' => $footballSeason->id,
            'role' => PlayerRole::GOALKEEPER,
        ]);

        $nomination = app(StartAuctionNomination::class)->execute(
            $auction,
            $playerSeason
        );

        $this->assertNull($nomination);

        $this->assertDatabaseHas('auction_turn_skips', [
            'auction_id' => $auction->id,
            'auction_participant_id' => $participant->id,
            'turn_number' => 1,
            'reason' => AuctionTurnSkipReason::NO_CREDITS->value,
        ]);

        $this->assertDatabaseCount('auction_nominations', 0);
    }

    public function test_it_fails_when_player_is_already_actively_owned_in_league_season(): void
    {
        $auction = $this->createStartedAuction();

        $participant = $auction->participants()
            ->with('team')
            ->firstOrFail();

        $footballSeason = $this->createFootballSeasonForAuction($auction);

        $playerSeason = PlayerSeason::factory()->create([
            'football_season_id' => $footballSeason->id,
            'role' => PlayerRole::GOALKEEPER,
        ]);

        RosterOwnership::factory()->create([
            'league_season_id' => $auction->marketSession->league_season_id,
            'team_id' => $participant->team_id,
            'player_season_id' => $playerSeason->id,
            'acquisition_value' => 10,
            'acquired_at' => now(),
            'released_at' => null,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Player is already actively owned in this league season.'
        );

        app(StartAuctionNomination::class)->execute(
            $auction,
            $playerSeason
        );
    }

    public function test_it_fails_when_player_is_already_in_an_active_nomination(): void
    {
        $auction = $this->createStartedAuction();

        $footballSeason = $this->createFootballSeasonForAuction($auction);

        $playerSeason = PlayerSeason::factory()->create([
            'football_season_id' => $footballSeason->id,
            'role' => PlayerRole::GOALKEEPER,
        ]);

        app(StartAuctionNomination::class)->execute(
            $auction,
            $playerSeason
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Player is already in an active nomination.'
        );

        app(StartAuctionNomination::class)->execute(
            $auction,
            $playerSeason
        );
    }

    public function test_it_allows_a_rejected_player_to_be_nominated_again(): void
    {
        $auction = $this->createStartedAuction();

        $footballSeason = $this->createFootballSeasonForAuction($auction);

        $playerSeason = PlayerSeason::factory()->create([
            'football_season_id' => $footballSeason->id,
            'role' => PlayerRole::GOALKEEPER,
        ]);

        $firstNomination = app(StartAuctionNomination::class)->execute(
            $auction,
            $playerSeason
        );

        $firstNomination->update([
            'status' => AuctionNominationStatus::REJECTED,
            'closed_at' => now(),
            'close_reason' => AuctionNominationCloseReason::PRESIDENT_REJECTED,
        ]);

        $secondNomination = app(StartAuctionNomination::class)->execute(
            $auction,
            $playerSeason
        );

        $this->assertNotNull($secondNomination);

        $this->assertSame(
            AuctionNominationStatus::ACTIVE,
            $secondNomination->status
        );

        $this->assertSame(
            $playerSeason->id,
            $secondNomination->player_season_id
        );

        $this->assertSame(2, $secondNomination->turn_number);
    }

    public function test_it_fails_when_auction_has_no_active_role_phase(): void
    {
        $auction = $this->createStartedAuction();

        $auction->rolePhases()
            ->where('status', AuctionRolePhaseStatus::ACTIVE)
            ->update([
                'status' => AuctionRolePhaseStatus::COMPLETED,
            ]);

        $footballSeason = $this->createFootballSeasonForAuction($auction);

        $playerSeason = PlayerSeason::factory()->create([
            'football_season_id' => $footballSeason->id,
            'role' => PlayerRole::GOALKEEPER,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Auction has no active role phase.'
        );

        app(StartAuctionNomination::class)->execute(
            $auction,
            $playerSeason
        );
    }

    public function test_it_fails_when_player_belongs_to_a_different_football_season(): void
    {
        $auction = $this->createStartedAuction();

        $leagueSeason = $auction->marketSession->leagueSeason;

        $footballSeason = FootballSeason::factory()->create([
            'start_year' => $leagueSeason->start_year - 1,
            'end_year' => $leagueSeason->end_year - 1,
            'name' => 'Previous Serie A season',
        ]);

        $playerSeason = PlayerSeason::factory()->create([
            'football_season_id' => $footballSeason->id,
            'role' => PlayerRole::GOALKEEPER,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Player does not belong to the football season of this league season.'
        );

        app(StartAuctionNomination::class)->execute(
            $auction,
            $playerSeason
        );
    }

    private function createFootballSeasonForAuction(Auction $auction): FootballSeason
    {
        $leagueSeason = $auction->marketSession->leagueSeason;

        return FootballSeason::factory()->create([
            'start_year' => $leagueSeason->start_year,
            'end_year' => $leagueSeason->end_year,
            'name' => sprintf(
                'Serie A %d/%d',
                $leagueSeason->start_year,
                $leagueSeason->end_year
            ),
        ]);
    }
}
