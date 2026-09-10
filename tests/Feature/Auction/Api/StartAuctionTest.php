<?php

namespace Tests\Feature\Auction\Api;

use App\Domain\Market\Enums\MarketCapabilityType;
use App\Domain\Market\Enums\MarketSessionStatus;
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

class StartAuctionTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_league_president_can_start_initialized_auction(): void
    {
        $auction = Auction::factory()->create();

        $auction->marketSession->update([
            'status' => MarketSessionStatus::OPEN,
        ]);

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
            "/api/auctions/{$auction->ulid}/start"
        );

        $response
            ->assertOk()
            ->assertJsonPath('data.ulid', $auction->ulid)
            ->assertJsonPath('data.status', 'live');
    }

    public function test_active_league_member_cannot_start_initialized_auction(): void
    {
        $auction = Auction::factory()->create();

        $auction->marketSession->update([
            'status' => MarketSessionStatus::OPEN,
        ]);

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

        $president = User::factory()->create();

        LeagueMembership::factory()
            ->president()
            ->create([
                'league_id' => $league->id,
                'user_id' => $president->id,
            ]);

        Sanctum::actingAs($president);

        $this->postJson(
            "/api/auctions/{$auction->ulid}/initialize"
        )->assertOk();

        Sanctum::actingAs($member);

        $response = $this->postJson(
            "/api/auctions/{$auction->ulid}/start"
        );

        $response->assertForbidden();
    }

    public function test_inactive_league_president_cannot_start_initialized_auction(): void
    {
        $auction = Auction::factory()->create();

        $auction->marketSession->update([
            'status' => MarketSessionStatus::OPEN,
        ]);

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
            "/api/auctions/{$auction->ulid}/start"
        );

        $response->assertForbidden();
    }

    public function test_user_outside_league_cannot_start_auction(): void
    {
        $auction = Auction::factory()->create();

        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson(
            "/api/auctions/{$auction->ulid}/start"
        );

        $response->assertForbidden();
    }

    public function test_unauthenticated_user_cannot_start_auction(): void
    {
        $auction = Auction::factory()->create();

        $response = $this->postJson(
            "/api/auctions/{$auction->ulid}/start"
        );

        $response->assertUnauthorized();
    }

    public function test_president_cannot_start_auction_when_market_session_is_not_open(): void
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
            "/api/auctions/{$auction->ulid}/start"
        );

        $response
            ->assertUnprocessable()
            ->assertJson([
                'message' => 'Market session must be open before starting the auction.',
            ]);
    }

    public function test_president_cannot_start_auction_that_is_not_scheduled(): void
    {
        $auction = Auction::factory()->live()->create();

        $auction->marketSession->update([
            'status' => MarketSessionStatus::OPEN,
        ]);

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
            "/api/auctions/{$auction->ulid}/start"
        );

        $response
            ->assertUnprocessable()
            ->assertJson([
                'message' => 'Only scheduled auctions can be started.',
            ]);
    }

    public function test_president_cannot_start_auction_without_participants(): void
    {
        $auction = Auction::factory()->create();

        $auction->marketSession->update([
            'status' => MarketSessionStatus::OPEN,
        ]);

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
            "/api/auctions/{$auction->ulid}/start"
        );

        $response
            ->assertUnprocessable()
            ->assertJson([
                'message' => 'Auction has no participants.',
            ]);
    }
}
