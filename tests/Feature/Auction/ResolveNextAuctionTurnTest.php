<?php

namespace Tests\Feature\Auction;

use App\Domain\Auction\Actions\InitializeAuction;
use App\Domain\Auction\Actions\ResolveNextAuctionTurn;
use App\Domain\Auction\Actions\StartAuction;
use App\Domain\Auction\Enums\AuctionNominationStatus;
use App\Domain\Auction\Enums\AuctionRolePhaseStatus;
use App\Domain\Auction\Enums\AuctionTurnSkipReason;
use App\Domain\Football\Enums\PlayerRole;
use App\Domain\Market\Enums\MarketCapabilityType;
use App\Domain\Market\Enums\MarketSessionStatus;
use App\Models\Auction\Auction;
use App\Models\Auction\AuctionNomination;
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

class ResolveNextAuctionTurnTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_turn_belongs_to_participant_in_position_one(): void
    {
        $auction = $this->createStartedAuction(3);

        $result = app(ResolveNextAuctionTurn::class)->execute($auction);

        $this->assertSame(1, $result['turn_number']);
        $this->assertSame(
            1,
            $result['participant']->nomination_position
        );
    }

    public function test_second_turn_belongs_to_participant_in_position_two(): void
    {
        $auction = $this->createStartedAuction(3);

        $firstParticipant = $auction->participants()
            ->where('nomination_position', 1)
            ->firstOrFail();

        $activePhase = $auction->rolePhases()
            ->where('status', AuctionRolePhaseStatus::ACTIVE)
            ->firstOrFail();

        $footballSeason = FootballSeason::factory()->create([
            'start_year' => 2026,
            'end_year' => 2027,
            'name' => 'Serie A 2026/2027',
        ]);

        $playerSeason = PlayerSeason::factory()->create([
            'football_season_id' => $footballSeason->id,
        ]);

        AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'auction_role_phase_id' => $activePhase->id,
            'auction_participant_id' => $firstParticipant->id,
            'player_season_id' => $playerSeason->id,
            'turn_number' => 1,
            'status' => AuctionNominationStatus::COMPLETED,
        ]);

        $result = app(ResolveNextAuctionTurn::class)->execute($auction);

        $this->assertSame(2, $result['turn_number']);
        $this->assertSame(
            2,
            $result['participant']->nomination_position
        );
    }

    public function test_turn_order_cycles_back_to_position_one(): void
    {
        $auction = $this->createStartedAuction(3);

        $participants = $auction->participants()
            ->orderBy('nomination_position')
            ->get();

        $activePhase = $auction->rolePhases()
            ->where('status', AuctionRolePhaseStatus::ACTIVE)
            ->firstOrFail();

        $footballSeason = FootballSeason::factory()->create([
            'start_year' => 2026,
            'end_year' => 2027,
            'name' => 'Serie A 2026/2027',
        ]);

        foreach ($participants as $index => $participant) {
            $playerSeason = PlayerSeason::factory()->create([
                'football_season_id' => $footballSeason->id,
            ]);

            AuctionNomination::factory()->create([
                'auction_id' => $auction->id,
                'auction_role_phase_id' => $activePhase->id,
                'auction_participant_id' => $participant->id,
                'player_season_id' => $playerSeason->id,
                'turn_number' => $index + 1,
                'status' => AuctionNominationStatus::COMPLETED,
            ]);
        }

        $result = app(ResolveNextAuctionTurn::class)->execute($auction);

        $this->assertSame(4, $result['turn_number']);
        $this->assertSame(
            1,
            $result['participant']->nomination_position
        );
    }

    public function test_it_does_not_create_turn_skips_when_participant_is_eligible(): void
    {
        $auction = $this->createStartedAuction(3);

        app(ResolveNextAuctionTurn::class)->execute($auction);

        $this->assertDatabaseCount('auction_turn_skips', 0);
    }

    public function test_it_skips_participant_when_current_role_is_complete(): void
    {
        $auction = $this->createStartedAuction(2);

        $firstParticipant = $auction->participants()
            ->where('nomination_position', 1)
            ->firstOrFail();

        $leagueSeason = $auction->marketSession->leagueSeason;

        $footballSeason = FootballSeason::factory()->create([
            'start_year' => 2026,
            'end_year' => 2027,
            'name' => 'Serie A 2026/2027',
        ]);

        for ($i = 0; $i < 3; $i++) {
            $playerSeason = PlayerSeason::factory()->create([
                'football_season_id' => $footballSeason->id,
                'role' => PlayerRole::GOALKEEPER,
            ]);

            RosterOwnership::factory()->create([
                'league_season_id' => $leagueSeason->id,
                'team_id' => $firstParticipant->team_id,
                'player_season_id' => $playerSeason->id,
                'released_at' => null,
            ]);
        }

        $result = app(ResolveNextAuctionTurn::class)->execute($auction);

        $this->assertSame(2, $result['turn_number']);

        $this->assertSame(
            2,
            $result['participant']->nomination_position
        );

        $this->assertDatabaseHas('auction_turn_skips', [
            'auction_id' => $auction->id,
            'auction_participant_id' => $firstParticipant->id,
            'turn_number' => 1,
            'reason' => AuctionTurnSkipReason::ROLE_COMPLETE->value,
        ]);
    }

    public function test_it_skips_participant_when_roster_is_complete(): void
    {
        $auction = $this->createStartedAuction(2);

        $firstParticipant = $auction->participants()
            ->where('nomination_position', 1)
            ->firstOrFail();

        $leagueSeason = $auction->marketSession->leagueSeason;

        $footballSeason = FootballSeason::factory()->create([
            'start_year' => 2026,
            'end_year' => 2027,
            'name' => 'Serie A 2026/2027',
        ]);

        $leagueSeason->rosterRules()->delete();

        $rules = [
            PlayerRole::GOALKEEPER->value => 1,
            PlayerRole::DEFENDER->value => 1,
            PlayerRole::MIDFIELDER->value => 1,
            PlayerRole::FORWARD->value => 1,
        ];

        foreach ($rules as $role => $maxPlayers) {
            LeagueSeasonRosterRule::factory()->create([
                'league_season_id' => $leagueSeason->id,
                'role' => $role,
                'max_players' => $maxPlayers,
            ]);

            $playerSeason = PlayerSeason::factory()->create([
                'football_season_id' => $footballSeason->id,
                'role' => $role,
            ]);

            RosterOwnership::factory()->create([
                'league_season_id' => $leagueSeason->id,
                'team_id' => $firstParticipant->team_id,
                'player_season_id' => $playerSeason->id,
                'released_at' => null,
            ]);
        }

        $result = app(ResolveNextAuctionTurn::class)->execute($auction);

        $this->assertSame(2, $result['turn_number']);

        $this->assertSame(
            2,
            $result['participant']->nomination_position
        );

        $this->assertDatabaseHas('auction_turn_skips', [
            'auction_id' => $auction->id,
            'auction_participant_id' => $firstParticipant->id,
            'turn_number' => 1,
            'reason' => AuctionTurnSkipReason::ROSTER_COMPLETE->value,
        ]);
    }

    private function createStartedAuction(int $teamsCount): Auction
    {
        $auction = Auction::factory()->create();

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

        for ($i = 0; $i < $teamsCount; $i++) {
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
        }

        $auction = app(InitializeAuction::class)->execute($auction);

        return app(StartAuction::class)->execute($auction);
    }

    public function test_it_fails_when_participant_has_no_credit_account(): void
    {
        $auction = $this->createStartedAuction(1);

        $participant = $auction->participants()
            ->where('nomination_position', 1)
            ->firstOrFail();

        $participant->team->creditAccount()->delete();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Auction participant team has no credit account.'
        );

        app(ResolveNextAuctionTurn::class)->execute($auction);
    }

    public function test_it_skips_participant_with_no_credits_and_moves_to_next_one(): void
    {
        $auction = $this->createStartedAuction(2);

        $firstParticipant = $auction->participants()
            ->where('nomination_position', 1)
            ->firstOrFail();

        $firstParticipant->team->creditAccount()->update([
            'current_balance' => 0,
        ]);

        $result = app(ResolveNextAuctionTurn::class)->execute($auction);

        $this->assertSame(2, $result['turn_number']);
        $this->assertSame(
            2,
            $result['participant']->nomination_position
        );

        $this->assertDatabaseHas('auction_turn_skips', [
            'auction_id' => $auction->id,
            'auction_participant_id' => $firstParticipant->id,
            'turn_number' => 1,
            'reason' => AuctionTurnSkipReason::NO_CREDITS->value,
        ]);
    }

    public function test_it_returns_null_after_a_full_cycle_when_no_participant_is_eligible(): void
    {
        $auction = $this->createStartedAuction(3);

        $participants = $auction->participants()
            ->with('team.creditAccount')
            ->orderBy('nomination_position')
            ->get();

        foreach ($participants as $participant) {
            $participant->team->creditAccount->update([
                'current_balance' => 0,
            ]);
        }

        $result = app(ResolveNextAuctionTurn::class)->execute($auction);

        $this->assertNull($result);

        $this->assertDatabaseCount('auction_turn_skips', 3);

        foreach ($participants as $index => $participant) {
            $this->assertDatabaseHas('auction_turn_skips', [
                'auction_id' => $auction->id,
                'auction_participant_id' => $participant->id,
                'turn_number' => $index + 1,
                'reason' => AuctionTurnSkipReason::NO_CREDITS->value,
            ]);
        }
    }
}
