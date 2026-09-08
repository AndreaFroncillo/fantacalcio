<?php

namespace Tests\Feature\Auction;

use App\Domain\Auction\Actions\AdvanceAuctionLifecycle;
use App\Domain\Auction\Enums\AuctionRolePhaseStatus;
use App\Domain\Auction\Enums\AuctionStatus;
use App\Domain\Auction\Enums\AuctionTurnSkipReason;
use App\Domain\Football\Enums\PlayerRole;
use App\Models\Auction\Auction;
use App\Models\Auction\AuctionParticipant;
use App\Models\Auction\AuctionRolePhase;
use App\Models\Credit\TeamCreditAccount;
use App\Models\Roster\LeagueSeasonRosterRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvanceAuctionLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_keeps_current_phase_active_when_an_eligible_participant_exists(): void
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

        $result = app(AdvanceAuctionLifecycle::class)
            ->execute($auction);

        $this->assertSame(
            AuctionStatus::LIVE,
            $result->fresh()->status
        );

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

        $this->assertDatabaseCount(
            'auction_turn_skips',
            0
        );
    }

    public function test_it_advances_to_next_role_phase_when_no_participant_is_eligible(): void
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

        $result = app(AdvanceAuctionLifecycle::class)
            ->execute($auction);

        $this->assertSame(
            AuctionStatus::LIVE,
            $result->fresh()->status
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

        $this->assertDatabaseCount(
            'auction_turn_skips',
            2
        );

        $this->assertDatabaseHas('auction_turn_skips', [
            'auction_id' => $auction->id,
            'auction_participant_id' => $firstParticipant->id,
            'reason' => AuctionTurnSkipReason::NO_CREDITS->value,
            'turn_number' => 1,
        ]);

        $this->assertDatabaseHas('auction_turn_skips', [
            'auction_id' => $auction->id,
            'auction_participant_id' => $secondParticipant->id,
            'reason' => AuctionTurnSkipReason::NO_CREDITS->value,
            'turn_number' => 2,
        ]);
    }

    public function test_it_completes_auction_when_last_role_phase_has_no_eligible_participants(): void
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

        AuctionRolePhase::factory()->create([
            'auction_id' => $auction->id,
            'role' => PlayerRole::GOALKEEPER,
            'position' => 1,
            'status' => AuctionRolePhaseStatus::COMPLETED,
            'completed_at' => now(),
        ]);

        AuctionRolePhase::factory()->create([
            'auction_id' => $auction->id,
            'role' => PlayerRole::DEFENDER,
            'position' => 2,
            'status' => AuctionRolePhaseStatus::COMPLETED,
            'completed_at' => now(),
        ]);

        AuctionRolePhase::factory()->create([
            'auction_id' => $auction->id,
            'role' => PlayerRole::MIDFIELDER,
            'position' => 3,
            'status' => AuctionRolePhaseStatus::COMPLETED,
            'completed_at' => now(),
        ]);

        $result = app(AdvanceAuctionLifecycle::class)
            ->execute($auction);

        $this->assertSame(
            AuctionRolePhaseStatus::COMPLETED,
            $forwardPhase->fresh()->status
        );

        $this->assertNotNull(
            $forwardPhase->fresh()->completed_at
        );

        $this->assertSame(
            AuctionStatus::COMPLETED,
            $result->fresh()->status
        );

        $this->assertNotNull(
            $result->fresh()->completed_at
        );
    }

    public function test_it_records_skips_before_finding_next_eligible_participant(): void
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
            'current_balance' => 100,
        ]);

        LeagueSeasonRosterRule::factory()->create([
            'league_season_id' => $auction
                ->marketSession
                ->league_season_id,
            'role' => PlayerRole::GOALKEEPER,
            'max_players' => 3,
        ]);

        $result = app(AdvanceAuctionLifecycle::class)
            ->execute($auction);

        $this->assertSame(
            AuctionStatus::LIVE,
            $result->fresh()->status
        );

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

        $this->assertDatabaseCount(
            'auction_turn_skips',
            1
        );

        $this->assertDatabaseHas('auction_turn_skips', [
            'auction_id' => $auction->id,
            'auction_participant_id' => $firstParticipant->id,
            'reason' => AuctionTurnSkipReason::NO_CREDITS->value,
            'turn_number' => 1,
        ]);
    }
}
