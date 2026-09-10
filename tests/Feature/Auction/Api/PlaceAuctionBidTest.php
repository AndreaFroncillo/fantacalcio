<?php

namespace Tests\Feature\Auction\Api;

use App\Domain\League\Enums\LeagueMembershipStatus;
use App\Models\Auction\Auction;
use App\Models\Auction\AuctionNomination;
use App\Models\Auction\AuctionParticipant;
use App\Models\Auction\AuctionRolePhase;
use App\Models\Credit\TeamCreditAccount;
use App\Models\League\LeagueMembership;
use App\Models\Season\SeasonParticipation;
use App\Models\Team\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PlaceAuctionBidTest extends TestCase
{
    use RefreshDatabase;

    public function test_current_league_participant_can_place_bid(): void
    {
        $auction = Auction::factory()->live()->create();

        $leagueSeason = $auction
            ->marketSession
            ->leagueSeason;

        $user = User::factory()->create();

        $membership = LeagueMembership::factory()->create([
            'league_id' => $leagueSeason->league_id,
            'user_id' => $user->id,
        ]);

        $participation = SeasonParticipation::factory()->create([
            'league_season_id' => $leagueSeason->id,
            'league_membership_id' => $membership->id,
        ]);

        $team = Team::factory()->create([
            'season_participation_id' => $participation->id,
        ]);

        TeamCreditAccount::factory()->create([
            'team_id' => $team->id,
            'current_balance' => 500,
        ]);

        $participant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'team_id' => $team->id,
            'nomination_position' => 1,
        ]);

        $rolePhase = AuctionRolePhase::factory()
            ->active()
            ->create([
                'auction_id' => $auction->id,
                'position' => 1,
            ]);

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'auction_role_phase_id' => $rolePhase->id,
            'auction_participant_id' => $participant->id,
            'opening_price' => 1,
            'expires_at' => now()->addMinute(),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson(
            "/api/auctions/{$auction->ulid}/nominations/{$nomination->ulid}/bids",
            [
                'amount' => 25,
            ]
        );

        $response
            ->assertOk()
            ->assertJsonPath('data.amount', 25);

        $this->assertDatabaseHas('auction_bids', [
            'auction_nomination_id' => $nomination->id,
            'auction_participant_id' => $participant->id,
            'amount' => 25,
            'sequence_number' => 1,
        ]);
    }

    public function test_unauthenticated_user_cannot_place_bid(): void
    {
        $auction = Auction::factory()->live()->create();

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'expires_at' => now()->addMinute(),
        ]);

        $response = $this->postJson(
            "/api/auctions/{$auction->ulid}/nominations/{$nomination->ulid}/bids",
            [
                'amount' => 25,
            ]
        );

        $response->assertUnauthorized();

        $this->assertDatabaseCount('auction_bids', 0);
    }

    public function test_user_outside_league_cannot_place_bid(): void
    {
        $auction = Auction::factory()->live()->create();

        $participant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
        ]);

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'auction_participant_id' => $participant->id,
            'expires_at' => now()->addMinute(),
        ]);

        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson(
            "/api/auctions/{$auction->ulid}/nominations/{$nomination->ulid}/bids",
            [
                'amount' => 25,
            ]
        );

        $response->assertForbidden();

        $this->assertDatabaseCount('auction_bids', 0);
    }

    public function test_inactive_league_member_cannot_place_bid(): void
    {
        $auction = Auction::factory()->live()->create();

        $leagueSeason = $auction
            ->marketSession
            ->leagueSeason;

        $user = User::factory()->create();

        LeagueMembership::factory()->create([
            'league_id' => $leagueSeason->league_id,
            'user_id' => $user->id,
            'status' => LeagueMembershipStatus::INACTIVE,
        ]);

        $participant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
        ]);

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'auction_participant_id' => $participant->id,
            'expires_at' => now()->addMinute(),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson(
            "/api/auctions/{$auction->ulid}/nominations/{$nomination->ulid}/bids",
            [
                'amount' => 25,
            ]
        );

        $response->assertForbidden();

        $this->assertDatabaseCount('auction_bids', 0);
    }

    public function test_active_league_member_without_auction_participant_cannot_place_bid(): void
    {
        $auction = Auction::factory()->live()->create();

        $leagueSeason = $auction
            ->marketSession
            ->leagueSeason;

        $user = User::factory()->create();

        LeagueMembership::factory()->create([
            'league_id' => $leagueSeason->league_id,
            'user_id' => $user->id,
        ]);

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'expires_at' => now()->addMinute(),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson(
            "/api/auctions/{$auction->ulid}/nominations/{$nomination->ulid}/bids",
            [
                'amount' => 25,
            ]
        );

        $response
            ->assertUnprocessable()
            ->assertJsonPath(
                'message',
                'User is not an auction participant.'
            );

        $this->assertDatabaseCount('auction_bids', 0);
    }

    public function test_nomination_from_different_auction_returns_not_found(): void
    {
        $auction = Auction::factory()->live()->create();

        $leagueSeason = $auction
            ->marketSession
            ->leagueSeason;

        $user = User::factory()->create();

        $membership = LeagueMembership::factory()->create([
            'league_id' => $leagueSeason->league_id,
            'user_id' => $user->id,
        ]);

        $participation = SeasonParticipation::factory()->create([
            'league_season_id' => $leagueSeason->id,
            'league_membership_id' => $membership->id,
        ]);

        $team = Team::factory()->create([
            'season_participation_id' => $participation->id,
        ]);

        TeamCreditAccount::factory()->create([
            'team_id' => $team->id,
            'current_balance' => 500,
        ]);

        AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'team_id' => $team->id,
            'nomination_position' => 1,
        ]);

        $otherAuction = Auction::factory()->live()->create();

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $otherAuction->id,
            'expires_at' => now()->addMinute(),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson(
            "/api/auctions/{$auction->ulid}/nominations/{$nomination->ulid}/bids",
            [
                'amount' => 25,
            ]
        );

        $response->assertNotFound();

        $this->assertDatabaseCount('auction_bids', 0);
    }

    public function test_bid_amount_is_required(): void
    {
        $auction = Auction::factory()->live()->create();

        $leagueSeason = $auction
            ->marketSession
            ->leagueSeason;

        $user = User::factory()->create();

        $membership = LeagueMembership::factory()->create([
            'league_id' => $leagueSeason->league_id,
            'user_id' => $user->id,
        ]);

        $participation = SeasonParticipation::factory()->create([
            'league_season_id' => $leagueSeason->id,
            'league_membership_id' => $membership->id,
        ]);

        $team = Team::factory()->create([
            'season_participation_id' => $participation->id,
        ]);

        TeamCreditAccount::factory()->create([
            'team_id' => $team->id,
            'current_balance' => 500,
        ]);

        $participant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'team_id' => $team->id,
            'nomination_position' => 1,
        ]);

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'auction_participant_id' => $participant->id,
            'expires_at' => now()->addMinute(),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson(
            "/api/auctions/{$auction->ulid}/nominations/{$nomination->ulid}/bids",
            []
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'amount',
            ]);

        $this->assertDatabaseCount('auction_bids', 0);
    }

    public function test_bid_amount_must_be_an_integer(): void
    {
        $auction = Auction::factory()->live()->create();

        $leagueSeason = $auction
            ->marketSession
            ->leagueSeason;

        $user = User::factory()->create();

        $membership = LeagueMembership::factory()->create([
            'league_id' => $leagueSeason->league_id,
            'user_id' => $user->id,
        ]);

        $participation = SeasonParticipation::factory()->create([
            'league_season_id' => $leagueSeason->id,
            'league_membership_id' => $membership->id,
        ]);

        $team = Team::factory()->create([
            'season_participation_id' => $participation->id,
        ]);

        TeamCreditAccount::factory()->create([
            'team_id' => $team->id,
            'current_balance' => 500,
        ]);

        $participant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'team_id' => $team->id,
            'nomination_position' => 1,
        ]);

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'auction_participant_id' => $participant->id,
            'expires_at' => now()->addMinute(),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson(
            "/api/auctions/{$auction->ulid}/nominations/{$nomination->ulid}/bids",
            [
                'amount' => 'abc',
            ]
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'amount',
            ]);

        $this->assertDatabaseCount('auction_bids', 0);
    }

    public function test_bid_amount_must_be_at_least_one(): void
    {
        $auction = Auction::factory()->live()->create();

        $leagueSeason = $auction
            ->marketSession
            ->leagueSeason;

        $user = User::factory()->create();

        $membership = LeagueMembership::factory()->create([
            'league_id' => $leagueSeason->league_id,
            'user_id' => $user->id,
        ]);

        $participation = SeasonParticipation::factory()->create([
            'league_season_id' => $leagueSeason->id,
            'league_membership_id' => $membership->id,
        ]);

        $team = Team::factory()->create([
            'season_participation_id' => $participation->id,
        ]);

        TeamCreditAccount::factory()->create([
            'team_id' => $team->id,
            'current_balance' => 500,
        ]);

        $participant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'team_id' => $team->id,
            'nomination_position' => 1,
        ]);

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'auction_participant_id' => $participant->id,
            'expires_at' => now()->addMinute(),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson(
            "/api/auctions/{$auction->ulid}/nominations/{$nomination->ulid}/bids",
            [
                'amount' => 0,
            ]
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'amount',
            ]);

        $this->assertDatabaseCount('auction_bids', 0);
    }

    public function test_bid_cannot_exceed_available_credits(): void
    {
        $auction = Auction::factory()->live()->create();

        $leagueSeason = $auction
            ->marketSession
            ->leagueSeason;

        $user = User::factory()->create();

        $membership = LeagueMembership::factory()->create([
            'league_id' => $leagueSeason->league_id,
            'user_id' => $user->id,
        ]);

        $participation = SeasonParticipation::factory()->create([
            'league_season_id' => $leagueSeason->id,
            'league_membership_id' => $membership->id,
        ]);

        $team = Team::factory()->create([
            'season_participation_id' => $participation->id,
        ]);

        TeamCreditAccount::factory()->create([
            'team_id' => $team->id,
            'current_balance' => 20,
        ]);

        $participant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'team_id' => $team->id,
            'nomination_position' => 1,
        ]);

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'auction_participant_id' => $participant->id,
            'opening_price' => 1,
            'expires_at' => now()->addMinute(),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson(
            "/api/auctions/{$auction->ulid}/nominations/{$nomination->ulid}/bids",
            [
                'amount' => 25,
            ]
        );

        $response
            ->assertUnprocessable()
            ->assertJsonPath(
                'message',
                'Bid amount exceeds participant available credits.'
            );

        $this->assertDatabaseCount('auction_bids', 0);
    }

    public function test_participant_cannot_bid_twice_consecutively(): void
    {
        $auction = Auction::factory()->live()->create();

        $leagueSeason = $auction
            ->marketSession
            ->leagueSeason;

        $user = User::factory()->create();

        $membership = LeagueMembership::factory()->create([
            'league_id' => $leagueSeason->league_id,
            'user_id' => $user->id,
        ]);

        $participation = SeasonParticipation::factory()->create([
            'league_season_id' => $leagueSeason->id,
            'league_membership_id' => $membership->id,
        ]);

        $team = Team::factory()->create([
            'season_participation_id' => $participation->id,
        ]);

        TeamCreditAccount::factory()->create([
            'team_id' => $team->id,
            'current_balance' => 500,
        ]);

        $participant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'team_id' => $team->id,
            'nomination_position' => 1,
        ]);

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'auction_participant_id' => $participant->id,
            'opening_price' => 1,
            'expires_at' => now()->addMinute(),
        ]);

        Sanctum::actingAs($user);

        $this->postJson(
            "/api/auctions/{$auction->ulid}/nominations/{$nomination->ulid}/bids",
            [
                'amount' => 10,
            ]
        )->assertOk();

        $response = $this->postJson(
            "/api/auctions/{$auction->ulid}/nominations/{$nomination->ulid}/bids",
            [
                'amount' => 20,
            ]
        );

        $response
            ->assertUnprocessable()
            ->assertJsonPath(
                'message',
                'Participant cannot bid twice consecutively.'
            );

        $this->assertDatabaseCount('auction_bids', 1);

        $this->assertDatabaseHas('auction_bids', [
            'auction_nomination_id' => $nomination->id,
            'auction_participant_id' => $participant->id,
            'amount' => 10,
            'sequence_number' => 1,
        ]);
    }

    public function test_bid_must_be_greater_than_current_bid(): void
    {
        $auction = Auction::factory()->live()->create();

        $leagueSeason = $auction
            ->marketSession
            ->leagueSeason;

        $firstUser = User::factory()->create();

        $firstMembership = LeagueMembership::factory()->create([
            'league_id' => $leagueSeason->league_id,
            'user_id' => $firstUser->id,
        ]);

        $firstParticipation = SeasonParticipation::factory()->create([
            'league_season_id' => $leagueSeason->id,
            'league_membership_id' => $firstMembership->id,
        ]);

        $firstTeam = Team::factory()->create([
            'season_participation_id' => $firstParticipation->id,
        ]);

        TeamCreditAccount::factory()->create([
            'team_id' => $firstTeam->id,
            'current_balance' => 500,
        ]);

        $firstParticipant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'team_id' => $firstTeam->id,
            'nomination_position' => 1,
        ]);

        $secondUser = User::factory()->create();

        $secondMembership = LeagueMembership::factory()->create([
            'league_id' => $leagueSeason->league_id,
            'user_id' => $secondUser->id,
        ]);

        $secondParticipation = SeasonParticipation::factory()->create([
            'league_season_id' => $leagueSeason->id,
            'league_membership_id' => $secondMembership->id,
        ]);

        $secondTeam = Team::factory()->create([
            'season_participation_id' => $secondParticipation->id,
        ]);

        TeamCreditAccount::factory()->create([
            'team_id' => $secondTeam->id,
            'current_balance' => 500,
        ]);

        AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'team_id' => $secondTeam->id,
            'nomination_position' => 2,
        ]);

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'auction_participant_id' => $firstParticipant->id,
            'opening_price' => 1,
            'expires_at' => now()->addMinute(),
        ]);

        Sanctum::actingAs($firstUser);

        $this->postJson(
            "/api/auctions/{$auction->ulid}/nominations/{$nomination->ulid}/bids",
            [
                'amount' => 20,
            ]
        )->assertOk();

        Sanctum::actingAs($secondUser);

        $response = $this->postJson(
            "/api/auctions/{$auction->ulid}/nominations/{$nomination->ulid}/bids",
            [
                'amount' => 20,
            ]
        );

        $response
            ->assertUnprocessable()
            ->assertJsonPath(
                'message',
                'Bid amount must be greater than the current bid.'
            );

        $this->assertDatabaseCount('auction_bids', 1);

        $this->assertDatabaseHas('auction_bids', [
            'auction_nomination_id' => $nomination->id,
            'auction_participant_id' => $firstParticipant->id,
            'amount' => 20,
            'sequence_number' => 1,
        ]);
    }

    public function test_bid_cannot_be_placed_after_nomination_timer_expires(): void
    {
        $auction = Auction::factory()->live()->create();

        $leagueSeason = $auction
            ->marketSession
            ->leagueSeason;

        $user = User::factory()->create();

        $membership = LeagueMembership::factory()->create([
            'league_id' => $leagueSeason->league_id,
            'user_id' => $user->id,
        ]);

        $participation = SeasonParticipation::factory()->create([
            'league_season_id' => $leagueSeason->id,
            'league_membership_id' => $membership->id,
        ]);

        $team = Team::factory()->create([
            'season_participation_id' => $participation->id,
        ]);

        TeamCreditAccount::factory()->create([
            'team_id' => $team->id,
            'current_balance' => 500,
        ]);

        $participant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'team_id' => $team->id,
            'nomination_position' => 1,
        ]);

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'auction_participant_id' => $participant->id,
            'opening_price' => 1,
            'expires_at' => now()->subSecond(),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson(
            "/api/auctions/{$auction->ulid}/nominations/{$nomination->ulid}/bids",
            [
                'amount' => 25,
            ]
        );

        $response
            ->assertUnprocessable()
            ->assertJsonPath(
                'message',
                'Auction nomination timer has expired.'
            );

        $this->assertDatabaseCount('auction_bids', 0);
    }

    public function test_bid_cannot_be_placed_on_inactive_nomination(): void
    {
        $auction = Auction::factory()->live()->create();

        $leagueSeason = $auction
            ->marketSession
            ->leagueSeason;

        $user = User::factory()->create();

        $membership = LeagueMembership::factory()->create([
            'league_id' => $leagueSeason->league_id,
            'user_id' => $user->id,
        ]);

        $participation = SeasonParticipation::factory()->create([
            'league_season_id' => $leagueSeason->id,
            'league_membership_id' => $membership->id,
        ]);

        $team = Team::factory()->create([
            'season_participation_id' => $participation->id,
        ]);

        TeamCreditAccount::factory()->create([
            'team_id' => $team->id,
            'current_balance' => 500,
        ]);

        $participant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'team_id' => $team->id,
            'nomination_position' => 1,
        ]);

        $nomination = AuctionNomination::factory()
            ->confirmedByPresident()
            ->create([
                'auction_id' => $auction->id,
                'auction_participant_id' => $participant->id,
            ]);

        Sanctum::actingAs($user);

        $response = $this->postJson(
            "/api/auctions/{$auction->ulid}/nominations/{$nomination->ulid}/bids",
            [
                'amount' => 25,
            ]
        );

        $response
            ->assertUnprocessable()
            ->assertJsonPath(
                'message',
                'Auction nomination is not active.'
            );

        $this->assertDatabaseCount('auction_bids', 0);
    }

    public function test_bid_cannot_be_placed_when_auction_is_not_live(): void
    {
        $auction = Auction::factory()->create();

        $leagueSeason = $auction
            ->marketSession
            ->leagueSeason;

        $user = User::factory()->create();

        $membership = LeagueMembership::factory()->create([
            'league_id' => $leagueSeason->league_id,
            'user_id' => $user->id,
        ]);

        $participation = SeasonParticipation::factory()->create([
            'league_season_id' => $leagueSeason->id,
            'league_membership_id' => $membership->id,
        ]);

        $team = Team::factory()->create([
            'season_participation_id' => $participation->id,
        ]);

        TeamCreditAccount::factory()->create([
            'team_id' => $team->id,
            'current_balance' => 500,
        ]);

        $participant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'team_id' => $team->id,
            'nomination_position' => 1,
        ]);

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'auction_participant_id' => $participant->id,
            'opening_price' => 1,
            'expires_at' => now()->addMinute(),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson(
            "/api/auctions/{$auction->ulid}/nominations/{$nomination->ulid}/bids",
            [
                'amount' => 25,
            ]
        );

        $response
            ->assertUnprocessable()
            ->assertJsonPath(
                'message',
                'Auction is not live.'
            );

        $this->assertDatabaseCount('auction_bids', 0);
    }

    public function test_first_bid_must_be_at_least_opening_price(): void
    {
        $auction = Auction::factory()->live()->create();

        $leagueSeason = $auction
            ->marketSession
            ->leagueSeason;

        $user = User::factory()->create();

        $membership = LeagueMembership::factory()->create([
            'league_id' => $leagueSeason->league_id,
            'user_id' => $user->id,
        ]);

        $participation = SeasonParticipation::factory()->create([
            'league_season_id' => $leagueSeason->id,
            'league_membership_id' => $membership->id,
        ]);

        $team = Team::factory()->create([
            'season_participation_id' => $participation->id,
        ]);

        TeamCreditAccount::factory()->create([
            'team_id' => $team->id,
            'current_balance' => 500,
        ]);

        $participant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'team_id' => $team->id,
            'nomination_position' => 1,
        ]);

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'auction_participant_id' => $participant->id,
            'opening_price' => 10,
            'expires_at' => now()->addMinute(),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson(
            "/api/auctions/{$auction->ulid}/nominations/{$nomination->ulid}/bids",
            [
                'amount' => 5,
            ]
        );

        $response
            ->assertUnprocessable()
            ->assertJsonPath(
                'message',
                'Bid amount must be at least the opening price.'
            );

        $this->assertDatabaseCount('auction_bids', 0);
    }
}
