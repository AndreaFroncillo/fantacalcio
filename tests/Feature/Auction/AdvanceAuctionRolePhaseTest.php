<?php

namespace Tests\Feature\Auction;

use App\Domain\Auction\Actions\AdvanceAuctionRolePhase;
use App\Domain\Auction\Enums\AuctionNominationStatus;
use App\Domain\Auction\Enums\AuctionRolePhaseStatus;
use App\Domain\Auction\Enums\AuctionStatus;
use App\Domain\Auction\Enums\AuctionTurnSkipReason;
use App\Domain\Football\Enums\PlayerRole;
use App\Events\Auction\AuctionRolePhaseAdvanced;
use App\Models\Auction\Auction;
use App\Models\Auction\AuctionNomination;
use App\Models\Auction\AuctionParticipant;
use App\Models\Auction\AuctionRolePhase;
use App\Models\Credit\TeamCreditAccount;
use App\Models\Roster\LeagueSeasonRosterRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use RuntimeException;
use Tests\TestCase;

class AdvanceAuctionRolePhaseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([
            AuctionRolePhaseAdvanced::class,
        ]);
    }

    public function test_it_advances_to_next_role_phase_when_current_role_has_no_eligible_participants(): void
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

        $firstParticipant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'nomination_position' => 1,
        ]);

        $secondParticipant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'nomination_position' => 2,
        ]);

        TeamCreditAccount::factory()->create([
            'team_id' => $firstParticipant->team_id,
            'current_balance' => 0,
        ]);

        TeamCreditAccount::factory()->create([
            'team_id' => $secondParticipant->team_id,
            'current_balance' => 0,
        ]);

        LeagueSeasonRosterRule::factory()->create([
            'league_season_id' => $auction
                ->marketSession
                ->league_season_id,
            'role' => PlayerRole::GOALKEEPER,
            'max_players' => 3,
        ]);

        $nextPhase = app(AdvanceAuctionRolePhase::class)
            ->execute($auction);

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
            $defenderPhase->id,
            $nextPhase->id
        );
    }

    public function test_it_does_not_advance_when_current_role_still_has_eligible_participants(): void
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

        TeamCreditAccount::factory()->create([
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

        try {
            app(AdvanceAuctionRolePhase::class)
                ->execute($auction);

            $this->fail('Expected RuntimeException was not thrown.');
        } catch (RuntimeException $exception) {
            $this->assertSame(
                'Auction role phase still has eligible participants.',
                $exception->getMessage()
            );
        }

        $this->assertSame(
            AuctionRolePhaseStatus::ACTIVE,
            $goalkeeperPhase->fresh()->status
        );

        $this->assertNull(
            $goalkeeperPhase->fresh()->completed_at
        );

        $this->assertSame(
            AuctionRolePhaseStatus::PENDING,
            $defenderPhase->fresh()->status
        );
    }

    public function test_it_preserves_existing_skips_when_current_role_still_has_eligible_participants(): void
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

        $firstParticipant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'nomination_position' => 1,
        ]);

        $secondParticipant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'nomination_position' => 2,
        ]);

        TeamCreditAccount::factory()->create([
            'team_id' => $firstParticipant->team_id,
            'current_balance' => 0,
        ]);

        TeamCreditAccount::factory()->create([
            'team_id' => $secondParticipant->team_id,
            'current_balance' => 100,
        ]);

        LeagueSeasonRosterRule::factory()->create([
            'league_season_id' => $auction
                ->marketSession
                ->league_season_id,
            'role' => PlayerRole::GOALKEEPER,
            'max_players' => 3,
        ]);

        $auction->turnSkips()->create([
            'auction_role_phase_id' => $goalkeeperPhase->id,
            'auction_participant_id' => $firstParticipant->id,
            'turn_number' => 1,
            'reason' => AuctionTurnSkipReason::NO_CREDITS,
            'performed_by_user_id' => null,
            'note' => null,
        ]);

        try {
            app(AdvanceAuctionRolePhase::class)
                ->execute($auction);

            $this->fail('Expected RuntimeException was not thrown.');
        } catch (RuntimeException $exception) {
            $this->assertSame(
                'Auction role phase still has eligible participants.',
                $exception->getMessage()
            );
        }

        $this->assertDatabaseHas('auction_turn_skips', [
            'auction_id' => $auction->id,
            'auction_role_phase_id' => $goalkeeperPhase->id,
            'auction_participant_id' => $firstParticipant->id,
            'turn_number' => 1,
            'reason' => AuctionTurnSkipReason::NO_CREDITS->value,
        ]);

        $this->assertDatabaseCount(
            'auction_turn_skips',
            1
        );

        $this->assertSame(
            AuctionRolePhaseStatus::ACTIVE,
            $goalkeeperPhase->fresh()->status
        );
    }

    public function test_it_fails_when_auction_has_active_nomination(): void
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

        AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'nomination_position' => 1,
        ]);

        AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'auction_role_phase_id' => $goalkeeperPhase->id,
            'status' => AuctionNominationStatus::ACTIVE,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Auction has an active nomination.'
        );

        app(AdvanceAuctionRolePhase::class)
            ->execute($auction);
    }

    public function test_it_fails_when_auction_is_not_live(): void
    {
        $auction = Auction::factory()->create();

        $goalkeeperPhase = AuctionRolePhase::factory()
            ->active()
            ->create([
                'auction_id' => $auction->id,
                'role' => PlayerRole::GOALKEEPER,
                'position' => 1,
            ]);

        try {
            app(AdvanceAuctionRolePhase::class)
                ->execute($auction);

            $this->fail('Expected RuntimeException was not thrown.');
        } catch (RuntimeException $exception) {
            $this->assertSame(
                'Auction is not live.',
                $exception->getMessage()
            );
        }

        $this->assertSame(
            AuctionRolePhaseStatus::ACTIVE,
            $goalkeeperPhase->fresh()->status
        );

        $this->assertNull(
            $goalkeeperPhase->fresh()->completed_at
        );
    }

    public function test_it_fails_when_auction_has_no_active_role_phase(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        AuctionRolePhase::factory()->create([
            'auction_id' => $auction->id,
            'role' => PlayerRole::GOALKEEPER,
            'position' => 1,
            'status' => AuctionRolePhaseStatus::PENDING,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Auction has no active role phase.'
        );

        app(AdvanceAuctionRolePhase::class)
            ->execute($auction);
    }

    public function test_it_completes_last_role_phase_and_returns_null(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        $forwardPhase = AuctionRolePhase::factory()
            ->active()
            ->create([
                'auction_id' => $auction->id,
                'role' => PlayerRole::FORWARD,
                'position' => 4,
            ]);

        $firstParticipant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'nomination_position' => 1,
        ]);

        $secondParticipant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'nomination_position' => 2,
        ]);

        TeamCreditAccount::factory()->create([
            'team_id' => $firstParticipant->team_id,
            'current_balance' => 0,
        ]);

        TeamCreditAccount::factory()->create([
            'team_id' => $secondParticipant->team_id,
            'current_balance' => 0,
        ]);

        LeagueSeasonRosterRule::factory()->create([
            'league_season_id' => $auction
                ->marketSession
                ->league_season_id,
            'role' => PlayerRole::FORWARD,
            'max_players' => 6,
        ]);

        $nextPhase = app(AdvanceAuctionRolePhase::class)
            ->execute($auction);

        $this->assertNull($nextPhase);

        $this->assertSame(
            AuctionRolePhaseStatus::COMPLETED,
            $forwardPhase->fresh()->status
        );

        $this->assertNotNull(
            $forwardPhase->fresh()->completed_at
        );

        $this->assertSame(
            AuctionStatus::LIVE,
            $auction->fresh()->status
        );

        $this->assertDatabaseMissing('auction_role_phases', [
            'auction_id' => $auction->id,
            'status' => AuctionRolePhaseStatus::ACTIVE->value,
        ]);
    }

    public function test_it_activates_the_next_pending_phase_by_position(): void
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

        $midfielderPhase = AuctionRolePhase::factory()->create([
            'auction_id' => $auction->id,
            'role' => PlayerRole::MIDFIELDER,
            'position' => 3,
            'status' => AuctionRolePhaseStatus::PENDING,
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

        TeamCreditAccount::factory()->create([
            'team_id' => $participant->team_id,
            'current_balance' => 0,
        ]);

        LeagueSeasonRosterRule::factory()->create([
            'league_season_id' => $auction
                ->marketSession
                ->league_season_id,
            'role' => PlayerRole::GOALKEEPER,
            'max_players' => 3,
        ]);

        $nextPhase = app(AdvanceAuctionRolePhase::class)
            ->execute($auction);

        $this->assertSame(
            $defenderPhase->id,
            $nextPhase->id
        );

        $this->assertSame(
            AuctionRolePhaseStatus::ACTIVE,
            $defenderPhase->fresh()->status
        );

        $this->assertSame(
            AuctionRolePhaseStatus::PENDING,
            $midfielderPhase->fresh()->status
        );

        $this->assertSame(
            AuctionRolePhaseStatus::COMPLETED,
            $goalkeeperPhase->fresh()->status
        );
    }

    public function test_it_preserves_existing_turn_skips_when_current_phase_is_completed(): void
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

        $firstParticipant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'nomination_position' => 1,
        ]);

        $secondParticipant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'nomination_position' => 2,
        ]);

        TeamCreditAccount::factory()->create([
            'team_id' => $firstParticipant->team_id,
            'current_balance' => 0,
        ]);

        TeamCreditAccount::factory()->create([
            'team_id' => $secondParticipant->team_id,
            'current_balance' => 0,
        ]);

        LeagueSeasonRosterRule::factory()->create([
            'league_season_id' => $auction
                ->marketSession
                ->league_season_id,
            'role' => PlayerRole::GOALKEEPER,
            'max_players' => 3,
        ]);

        $auction->turnSkips()->create([
            'auction_role_phase_id' => $goalkeeperPhase->id,
            'auction_participant_id' => $firstParticipant->id,
            'turn_number' => 1,
            'reason' => AuctionTurnSkipReason::NO_CREDITS,
            'performed_by_user_id' => null,
            'note' => null,
        ]);

        $auction->turnSkips()->create([
            'auction_role_phase_id' => $goalkeeperPhase->id,
            'auction_participant_id' => $secondParticipant->id,
            'turn_number' => 2,
            'reason' => AuctionTurnSkipReason::NO_CREDITS,
            'performed_by_user_id' => null,
            'note' => null,
        ]);

        $nextPhase = app(AdvanceAuctionRolePhase::class)
            ->execute($auction);

        $this->assertSame(
            $defenderPhase->id,
            $nextPhase->id
        );

        $this->assertSame(
            AuctionRolePhaseStatus::COMPLETED,
            $goalkeeperPhase->fresh()->status
        );

        $this->assertSame(
            AuctionRolePhaseStatus::ACTIVE,
            $defenderPhase->fresh()->status
        );

        $this->assertDatabaseCount(
            'auction_turn_skips',
            2
        );
    }

    public function test_it_does_not_create_turn_skips_when_checking_phase_eligibility(): void
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

        AuctionRolePhase::factory()->create([
            'auction_id' => $auction->id,
            'role' => PlayerRole::DEFENDER,
            'position' => 2,
            'status' => AuctionRolePhaseStatus::PENDING,
        ]);

        $firstParticipant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'nomination_position' => 1,
        ]);

        $secondParticipant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'nomination_position' => 2,
        ]);

        TeamCreditAccount::factory()->create([
            'team_id' => $firstParticipant->team_id,
            'current_balance' => 0,
        ]);

        TeamCreditAccount::factory()->create([
            'team_id' => $secondParticipant->team_id,
            'current_balance' => 0,
        ]);

        LeagueSeasonRosterRule::factory()->create([
            'league_season_id' => $auction
                ->marketSession
                ->league_season_id,
            'role' => PlayerRole::GOALKEEPER,
            'max_players' => 3,
        ]);

        app(AdvanceAuctionRolePhase::class)
            ->execute($auction);

        $this->assertDatabaseCount(
            'auction_turn_skips',
            0
        );

        $this->assertSame(
            AuctionRolePhaseStatus::COMPLETED,
            $goalkeeperPhase->fresh()->status
        );
    }

    public function test_it_dispatches_realtime_event_when_role_phase_advances(): void
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

        TeamCreditAccount::factory()->create([
            'team_id' => $participant->team_id,
            'current_balance' => 0,
        ]);

        LeagueSeasonRosterRule::factory()->create([
            'league_season_id' => $auction
                ->marketSession
                ->league_season_id,
            'role' => PlayerRole::GOALKEEPER,
            'max_players' => 3,
        ]);

        app(AdvanceAuctionRolePhase::class)
            ->execute($auction);

        Event::assertDispatched(
            AuctionRolePhaseAdvanced::class,
            function (AuctionRolePhaseAdvanced $event) use (
                $goalkeeperPhase,
                $defenderPhase
            ) {
                return $event->previousPhase->is($goalkeeperPhase)
                    && $event->nextPhase->is($defenderPhase);
            }
        );
    }

    public function test_it_does_not_dispatch_realtime_event_when_last_role_phase_completes(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        AuctionRolePhase::factory()
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

        TeamCreditAccount::factory()->create([
            'team_id' => $participant->team_id,
            'current_balance' => 0,
        ]);

        LeagueSeasonRosterRule::factory()->create([
            'league_season_id' => $auction
                ->marketSession
                ->league_season_id,
            'role' => PlayerRole::FORWARD,
            'max_players' => 6,
        ]);

        $result = app(AdvanceAuctionRolePhase::class)
            ->execute($auction);

        $this->assertNull($result);

        Event::assertNotDispatched(
            AuctionRolePhaseAdvanced::class
        );
    }
}
