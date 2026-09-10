<?php

namespace Tests\Feature\Auction\Api;

use App\Domain\Market\Enums\MarketCapabilityType;
use App\Models\Auction\Auction;
use App\Models\Credit\TeamCreditAccount;
use App\Models\League\LeagueMembership;
use App\Models\Market\MarketCapability;
use App\Models\Season\SeasonParticipation;
use App\Models\Team\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InitializeAuctionTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_league_president_can_initialize_auction(): void
    {
        $auction = Auction::factory()->create();

        $league = $auction
            ->marketSession
            ->leagueSeason
            ->league;

        $president = User::factory()->create();

        LeagueMembership::factory()
            ->president()
            ->create([
                'league_id' => $league->id,
                'user_id' => $president->id,
            ]);

        MarketCapability::factory()
            ->enabled()
            ->create([
                'market_session_id' => $auction->market_session_id,
                'type' => MarketCapabilityType::AUCTION,
            ]);

        $participation = SeasonParticipation::factory()->create([
            'league_season_id' => $auction
                ->marketSession
                ->leagueSeason
                ->id,
        ]);

        $team = Team::factory()->create([
            'season_participation_id' => $participation->id,
        ]);

        TeamCreditAccount::factory()->create([
            'team_id' => $team->id,
        ]);

        Sanctum::actingAs($president);

        $response = $this->postJson(
            "/api/auctions/{$auction->ulid}/initialize"
        );

        $response
            ->assertOk()
            ->assertJsonPath('data.ulid', $auction->ulid);

        $this->assertDatabaseCount('auction_role_phases', 4);
        $this->assertDatabaseCount('auction_participants', 1);
    }

    public function test_active_league_member_cannot_initialize_auction(): void
    {
        $auction = Auction::factory()->create();

        $league = $auction
            ->marketSession
            ->leagueSeason
            ->league;

        $member = User::factory()->create();

        LeagueMembership::factory()->create([
            'league_id' => $league->id,
            'user_id' => $member->id,
        ]);

        MarketCapability::factory()
            ->enabled()
            ->create([
                'market_session_id' => $auction->market_session_id,
                'type' => MarketCapabilityType::AUCTION,
            ]);

        $participation = SeasonParticipation::factory()->create([
            'league_season_id' => $auction
                ->marketSession
                ->leagueSeason
                ->id,
        ]);

        $team = Team::factory()->create([
            'season_participation_id' => $participation->id,
        ]);

        TeamCreditAccount::factory()->create([
            'team_id' => $team->id,
        ]);

        Sanctum::actingAs($member);

        $response = $this->postJson(
            "/api/auctions/{$auction->ulid}/initialize"
        );

        $response->assertForbidden();

        $this->assertDatabaseCount('auction_role_phases', 0);
        $this->assertDatabaseCount('auction_participants', 0);
    }

    public function test_inactive_league_president_cannot_initialize_auction(): void
    {
        $auction = Auction::factory()->create();

        $league = $auction
            ->marketSession
            ->leagueSeason
            ->league;

        $president = User::factory()->create();

        LeagueMembership::factory()
            ->president()
            ->inactive()
            ->create([
                'league_id' => $league->id,
                'user_id' => $president->id,
            ]);

        Sanctum::actingAs($president);

        $response = $this->postJson(
            "/api/auctions/{$auction->ulid}/initialize"
        );

        $response->assertForbidden();

        $this->assertDatabaseCount('auction_role_phases', 0);
        $this->assertDatabaseCount('auction_participants', 0);
    }

    public function test_user_outside_league_cannot_initialize_auction(): void
    {
        $auction = Auction::factory()->create();

        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson(
            "/api/auctions/{$auction->ulid}/initialize"
        );

        $response->assertForbidden();

        $this->assertDatabaseCount('auction_role_phases', 0);
        $this->assertDatabaseCount('auction_participants', 0);
    }

    public function test_unauthenticated_user_cannot_initialize_auction(): void
    {
        $auction = Auction::factory()->create();

        $response = $this->postJson(
            "/api/auctions/{$auction->ulid}/initialize"
        );

        $response->assertUnauthorized();

        $this->assertDatabaseCount('auction_role_phases', 0);
        $this->assertDatabaseCount('auction_participants', 0);
    }

    public function test_president_cannot_initialize_auction_that_is_not_scheduled(): void
    {
        $auction = Auction::factory()->live()->create();

        $league = $auction
            ->marketSession
            ->leagueSeason
            ->league;

        $president = User::factory()->create();

        LeagueMembership::factory()
            ->president()
            ->create([
                'league_id' => $league->id,
                'user_id' => $president->id,
            ]);

        Sanctum::actingAs($president);

        $response = $this->postJson(
            "/api/auctions/{$auction->ulid}/initialize"
        );

        $response
            ->assertUnprocessable()
            ->assertJson([
                'message' => 'Only scheduled auctions can be initialized.',
            ]);

        $this->assertDatabaseCount('auction_role_phases', 0);
        $this->assertDatabaseCount('auction_participants', 0);
    }

    public function test_president_cannot_initialize_auction_when_auction_capability_is_disabled(): void
    {
        $auction = Auction::factory()->create();

        $league = $auction
            ->marketSession
            ->leagueSeason
            ->league;

        $president = User::factory()->create();

        LeagueMembership::factory()
            ->president()
            ->create([
                'league_id' => $league->id,
                'user_id' => $president->id,
            ]);

        Sanctum::actingAs($president);

        $response = $this->postJson(
            "/api/auctions/{$auction->ulid}/initialize"
        );

        $response
            ->assertUnprocessable()
            ->assertJson([
                'message' => 'Auction capability is not enabled.',
            ]);

        $this->assertDatabaseCount('auction_role_phases', 0);
        $this->assertDatabaseCount('auction_participants', 0);
    }

    public function test_president_cannot_initialize_auction_without_active_teams(): void
    {
        $auction = Auction::factory()->create();

        $league = $auction
            ->marketSession
            ->leagueSeason
            ->league;

        $president = User::factory()->create();

        LeagueMembership::factory()
            ->president()
            ->create([
                'league_id' => $league->id,
                'user_id' => $president->id,
            ]);

        MarketCapability::factory()
            ->enabled()
            ->create([
                'market_session_id' => $auction->market_session_id,
                'type' => MarketCapabilityType::AUCTION,
            ]);

        Sanctum::actingAs($president);

        $response = $this->postJson(
            "/api/auctions/{$auction->ulid}/initialize"
        );

        $response
            ->assertUnprocessable()
            ->assertJson([
                'message' => 'Auction cannot be initialized without active teams.',
            ]);

        $this->assertDatabaseCount('auction_role_phases', 0);
        $this->assertDatabaseCount('auction_participants', 0);
    }

    public function test_president_cannot_initialize_same_auction_twice(): void
    {
        $auction = Auction::factory()->create();

        $league = $auction
            ->marketSession
            ->leagueSeason
            ->league;

        $president = User::factory()->create();

        LeagueMembership::factory()
            ->president()
            ->create([
                'league_id' => $league->id,
                'user_id' => $president->id,
            ]);

        MarketCapability::factory()
            ->enabled()
            ->create([
                'market_session_id' => $auction->market_session_id,
                'type' => MarketCapabilityType::AUCTION,
            ]);

        $participation = SeasonParticipation::factory()->create([
            'league_season_id' => $auction
                ->marketSession
                ->leagueSeason
                ->id,
        ]);

        $team = Team::factory()->create([
            'season_participation_id' => $participation->id,
        ]);

        TeamCreditAccount::factory()->create([
            'team_id' => $team->id,
        ]);

        Sanctum::actingAs($president);

        $this->postJson(
            "/api/auctions/{$auction->ulid}/initialize"
        )->assertOk();

        $response = $this->postJson(
            "/api/auctions/{$auction->ulid}/initialize"
        );

        $response
            ->assertUnprocessable()
            ->assertJson([
                'message' => 'Auction has already been initialized.',
            ]);

        $this->assertDatabaseCount('auction_role_phases', 4);
        $this->assertDatabaseCount('auction_participants', 1);
    }
}
