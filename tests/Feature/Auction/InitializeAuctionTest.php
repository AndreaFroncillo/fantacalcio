<?php

namespace Tests\Feature\Auction;

use App\Domain\Auction\Actions\InitializeAuction;
use App\Domain\Auction\Enums\AuctionRolePhaseStatus;
use App\Domain\Auction\Enums\AuctionStatus;
use App\Domain\Football\Enums\PlayerRole;
use App\Domain\Market\Enums\MarketCapabilityType;
use App\Domain\Season\Enums\SeasonParticipationStatus;
use App\Domain\Team\Enums\TeamStatus;
use App\Models\Auction\Auction;
use App\Models\Market\MarketCapability;
use App\Models\Season\SeasonParticipation;
use App\Models\Team\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class InitializeAuctionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_initializes_a_scheduled_auction(): void
    {
        $auction = Auction::factory()->create();

        MarketCapability::factory()->create([
            'market_session_id' => $auction->market_session_id,
            'type' => MarketCapabilityType::AUCTION,
            'is_enabled' => true,
        ]);

        $participation = SeasonParticipation::factory()->create([
            'league_season_id' => $auction->marketSession->league_season_id,
        ]);

        Team::factory()->create([
            'season_participation_id' => $participation->id,
        ]);

        $result = app(InitializeAuction::class)->execute($auction);

        $this->assertCount(4, $result->rolePhases);
        $this->assertCount(1, $result->participants);
    }

    public function test_it_creates_role_phases_in_the_expected_order(): void
    {
        $auction = Auction::factory()->create();

        MarketCapability::factory()->create([
            'market_session_id' => $auction->market_session_id,
            'type' => MarketCapabilityType::AUCTION,
            'is_enabled' => true,
        ]);

        $participation = SeasonParticipation::factory()->create([
            'league_season_id' => $auction->marketSession->league_season_id,
        ]);

        Team::factory()->create([
            'season_participation_id' => $participation->id,
        ]);

        $result = app(InitializeAuction::class)->execute($auction);

        $phases = $result->rolePhases->values();

        $this->assertSame(PlayerRole::GOALKEEPER, $phases[0]->role);
        $this->assertSame(1, $phases[0]->position);

        $this->assertSame(PlayerRole::DEFENDER, $phases[1]->role);
        $this->assertSame(2, $phases[1]->position);

        $this->assertSame(PlayerRole::MIDFIELDER, $phases[2]->role);
        $this->assertSame(3, $phases[2]->position);

        $this->assertSame(PlayerRole::FORWARD, $phases[3]->role);
        $this->assertSame(4, $phases[3]->position);

        foreach ($phases as $phase) {
            $this->assertSame(
                AuctionRolePhaseStatus::PENDING,
                $phase->status
            );
        }
    }

    public function test_it_creates_a_participant_for_each_active_team_of_the_season(): void
    {
        $auction = Auction::factory()->create();

        MarketCapability::factory()->create([
            'market_session_id' => $auction->market_session_id,
            'type' => MarketCapabilityType::AUCTION,
            'is_enabled' => true,
        ]);

        $teams = collect();

        for ($i = 0; $i < 3; $i++) {
            $participation = SeasonParticipation::factory()->create([
                'league_season_id' => $auction->marketSession->league_season_id,
            ]);

            $teams->push(
                Team::factory()->create([
                    'season_participation_id' => $participation->id,
                ])
            );
        }

        $result = app(InitializeAuction::class)->execute($auction);

        $this->assertCount(3, $result->participants);

        $this->assertEqualsCanonicalizing(
            $teams->pluck('id')->all(),
            $result->participants->pluck('team_id')->all()
        );
    }

    public function test_it_assigns_unique_consecutive_nomination_positions(): void
    {
        $auction = Auction::factory()->create();

        MarketCapability::factory()->create([
            'market_session_id' => $auction->market_session_id,
            'type' => MarketCapabilityType::AUCTION,
            'is_enabled' => true,
        ]);

        for ($i = 0; $i < 5; $i++) {
            $participation = SeasonParticipation::factory()->create([
                'league_season_id' => $auction->marketSession->league_season_id,
            ]);

            Team::factory()->create([
                'season_participation_id' => $participation->id,
            ]);
        }

        $result = app(InitializeAuction::class)->execute($auction);

        $positions = $result->participants
            ->pluck('nomination_position')
            ->sort()
            ->values()
            ->all();

        $this->assertSame([1, 2, 3, 4, 5], $positions);
    }

    public function test_it_ignores_inactive_season_participations(): void
    {
        $auction = Auction::factory()->create();

        MarketCapability::factory()->create([
            'market_session_id' => $auction->market_session_id,
            'type' => MarketCapabilityType::AUCTION,
            'is_enabled' => true,
        ]);

        $activeParticipation = SeasonParticipation::factory()->create([
            'league_season_id' => $auction->marketSession->league_season_id,
            'status' => SeasonParticipationStatus::ACTIVE,
        ]);

        $activeTeam = Team::factory()->create([
            'season_participation_id' => $activeParticipation->id,
            'status' => TeamStatus::ACTIVE,
        ]);

        $inactiveParticipation = SeasonParticipation::factory()->create([
            'league_season_id' => $auction->marketSession->league_season_id,
            'status' => SeasonParticipationStatus::INACTIVE,
        ]);

        Team::factory()->create([
            'season_participation_id' => $inactiveParticipation->id,
            'status' => TeamStatus::ACTIVE,
        ]);

        $result = app(InitializeAuction::class)->execute($auction);

        $this->assertCount(1, $result->participants);
        $this->assertSame(
            $activeTeam->id,
            $result->participants->first()->team_id
        );
    }

    public function test_it_ignores_inactive_teams(): void
    {
        $auction = Auction::factory()->create();

        MarketCapability::factory()->create([
            'market_session_id' => $auction->market_session_id,
            'type' => MarketCapabilityType::AUCTION,
            'is_enabled' => true,
        ]);

        $activeParticipation = SeasonParticipation::factory()->create([
            'league_season_id' => $auction->marketSession->league_season_id,
            'status' => SeasonParticipationStatus::ACTIVE,
        ]);

        $activeTeam = Team::factory()->create([
            'season_participation_id' => $activeParticipation->id,
            'status' => TeamStatus::ACTIVE,
        ]);

        $inactiveTeamParticipation = SeasonParticipation::factory()->create([
            'league_season_id' => $auction->marketSession->league_season_id,
            'status' => SeasonParticipationStatus::ACTIVE,
        ]);

        Team::factory()->create([
            'season_participation_id' => $inactiveTeamParticipation->id,
            'status' => TeamStatus::INACTIVE,
        ]);

        $result = app(InitializeAuction::class)->execute($auction);

        $this->assertCount(1, $result->participants);
        $this->assertSame(
            $activeTeam->id,
            $result->participants->first()->team_id
        );
    }

    public function test_it_fails_when_there_are_no_active_teams(): void
    {
        $auction = Auction::factory()->create();

        MarketCapability::factory()->create([
            'market_session_id' => $auction->market_session_id,
            'type' => MarketCapabilityType::AUCTION,
            'is_enabled' => true,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Auction cannot be initialized without active teams.'
        );

        app(InitializeAuction::class)->execute($auction);
    }

    public function test_it_fails_when_auction_is_not_scheduled(): void
    {
        $auction = Auction::factory()->create([
            'status' => AuctionStatus::LIVE,
        ]);

        MarketCapability::factory()->create([
            'market_session_id' => $auction->market_session_id,
            'type' => MarketCapabilityType::AUCTION,
            'is_enabled' => true,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Only scheduled auctions can be initialized.'
        );

        app(InitializeAuction::class)->execute($auction);
    }

    public function test_it_fails_when_auction_capability_is_missing(): void
    {
        $auction = Auction::factory()->create();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Auction capability is not enabled.'
        );

        app(InitializeAuction::class)->execute($auction);
    }

    public function test_it_fails_when_auction_capability_is_disabled(): void
    {
        $auction = Auction::factory()->create();

        MarketCapability::factory()->create([
            'market_session_id' => $auction->market_session_id,
            'type' => MarketCapabilityType::AUCTION,
            'is_enabled' => false,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Auction capability is not enabled.'
        );

        app(InitializeAuction::class)->execute($auction);
    }

    public function test_it_cannot_initialize_the_same_auction_twice(): void
    {
        $auction = Auction::factory()->create();

        MarketCapability::factory()->create([
            'market_session_id' => $auction->market_session_id,
            'type' => MarketCapabilityType::AUCTION,
            'is_enabled' => true,
        ]);

        $participation = SeasonParticipation::factory()->create([
            'league_season_id' => $auction->marketSession->league_season_id,
            'status' => SeasonParticipationStatus::ACTIVE,
        ]);

        Team::factory()->create([
            'season_participation_id' => $participation->id,
            'status' => TeamStatus::ACTIVE,
        ]);

        $action = app(InitializeAuction::class);

        $action->execute($auction);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Auction has already been initialized.'
        );

        $action->execute($auction);
    }

    public function test_it_does_not_create_nominations_or_bids(): void
    {
        $auction = Auction::factory()->create();

        MarketCapability::factory()->create([
            'market_session_id' => $auction->market_session_id,
            'type' => MarketCapabilityType::AUCTION,
            'is_enabled' => true,
        ]);

        $participation = SeasonParticipation::factory()->create([
            'league_season_id' => $auction->marketSession->league_season_id,
            'status' => SeasonParticipationStatus::ACTIVE,
        ]);

        Team::factory()->create([
            'season_participation_id' => $participation->id,
            'status' => TeamStatus::ACTIVE,
        ]);

        app(InitializeAuction::class)->execute($auction);

        $this->assertDatabaseCount('auction_nominations', 0);
        $this->assertDatabaseCount('auction_bids', 0);
    }
}
