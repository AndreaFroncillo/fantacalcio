<?php

namespace Tests\Feature\Auction;

use App\Domain\Auction\Actions\HasEligibleAuctionParticipant;
use App\Domain\Auction\Enums\AuctionRolePhaseStatus;
use App\Models\Auction\Auction;
use App\Models\Auction\AuctionParticipant;
use App\Models\Auction\AuctionRolePhase;
use App\Models\Credit\TeamCreditAccount;
use App\Models\Roster\LeagueSeasonRosterRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuctionTurnEligibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_true_when_current_role_has_an_eligible_participant(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        $activePhase = AuctionRolePhase::factory()->create([
            'auction_id' => $auction->id,
            'status' => AuctionRolePhaseStatus::ACTIVE,
        ]);

        LeagueSeasonRosterRule::factory()->create([
            'league_season_id' => $auction
                ->marketSession
                ->league_season_id,
            'role' => $activePhase->role,
            'max_players' => 3,
        ]);

        $participant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'nomination_position' => 1,
        ]);

        TeamCreditAccount::factory()->create([
            'team_id' => $participant->team_id,
            'current_balance' => 100,
        ]);

        $hasEligibleParticipant = app(
            HasEligibleAuctionParticipant::class
        )->execute($auction);

        $this->assertTrue($hasEligibleParticipant);

        $this->assertDatabaseCount(
            'auction_turn_skips',
            0
        );
    }

    public function test_it_returns_false_when_no_participant_is_eligible_without_creating_skips(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        $activePhase = AuctionRolePhase::factory()->create([
            'auction_id' => $auction->id,
            'status' => AuctionRolePhaseStatus::ACTIVE,
        ]);

        LeagueSeasonRosterRule::factory()->create([
            'league_season_id' => $auction
                ->marketSession
                ->league_season_id,
            'role' => $activePhase->role,
            'max_players' => 3,
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

        $hasEligibleParticipant = app(
            HasEligibleAuctionParticipant::class
        )->execute($auction);

        $this->assertFalse($hasEligibleParticipant);

        $this->assertDatabaseCount(
            'auction_turn_skips',
            0
        );
    }
}
