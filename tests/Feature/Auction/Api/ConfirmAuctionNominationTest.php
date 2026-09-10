<?php

namespace Tests\Feature\Auction\Api;

use App\Domain\Auction\Enums\AuctionNominationStatus;
use App\Domain\League\Enums\LeagueMembershipRole;
use App\Domain\League\Enums\LeagueMembershipStatus;
use App\Models\Auction\Auction;
use App\Models\Auction\AuctionBid;
use App\Models\Auction\AuctionNomination;
use App\Models\Auction\AuctionParticipant;
use App\Models\Auction\AuctionRolePhase;
use App\Models\Credit\TeamCreditAccount;
use App\Models\Football\PlayerSeason;
use App\Models\League\League;
use App\Models\League\LeagueMembership;
use App\Models\Roster\LeagueSeasonRosterRule;
use App\Models\Season\SeasonParticipation;
use App\Models\Team\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ConfirmAuctionNominationTest extends TestCase
{
    use RefreshDatabase;

    public function test_league_president_can_confirm_active_nomination(): void
    {
        $auction = Auction::factory()->live()->create();

        $leagueSeason = $auction
            ->marketSession
            ->leagueSeason;

        $president = User::factory()->create();

        LeagueMembership::factory()
            ->president()
            ->create([
                'league_id' => $leagueSeason->league_id,
                'user_id' => $president->id,
            ]);

        $this->assertDatabaseHas('league_memberships', [
            'league_id' => $leagueSeason->league_id,
            'user_id' => $president->id,
            'role' => LeagueMembershipRole::PRESIDENT->value,
            'status' => LeagueMembershipStatus::ACTIVE->value,
        ]);

        $this->assertTrue(
            LeagueMembership::query()
                ->where('league_id', $auction->marketSession->leagueSeason->league_id)
                ->where('user_id', $president->id)
                ->where('role', LeagueMembershipRole::PRESIDENT)
                ->where('status', LeagueMembershipStatus::ACTIVE)
                ->exists()
        );

        $participantUser = User::factory()->create();

        $participantMembership = LeagueMembership::factory()->create([
            'league_id' => $leagueSeason->league_id,
            'user_id' => $participantUser->id,
        ]);

        $participation = SeasonParticipation::factory()->create([
            'league_season_id' => $leagueSeason->id,
            'league_membership_id' => $participantMembership->id,
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

        $phase = AuctionRolePhase::factory()->active()->create([
            'auction_id' => $auction->id,
            'position' => 1,
        ]);

        LeagueSeasonRosterRule::factory()->create([
            'league_season_id' => $leagueSeason->id,
            'role' => $phase->role,
        ]);

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'auction_role_phase_id' => $phase->id,
            'auction_participant_id' => $participant->id,
            'opening_price' => 1,
            'expires_at' => now()->addMinute(),
        ]);

        Sanctum::actingAs($president);

        $response = $this->postJson(
            "/api/auctions/{$auction->ulid}/nominations/{$nomination->ulid}/confirm"
        );

        $response
            ->assertOk()
            ->assertJsonPath('data.ulid', $nomination->ulid)
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath(
                'data.close_reason',
                'president_confirmed'
            );

        $nomination->refresh();

        $this->assertNotNull($nomination->closed_at);
        $this->assertSame($president->id, $nomination->closed_by_user_id);
    }

    public function test_normal_league_member_cannot_confirm_nomination(): void
    {
        $auction = Auction::factory()->live()->create();

        $leagueSeason = $auction
            ->marketSession
            ->leagueSeason;

        $member = User::factory()->create();

        LeagueMembership::factory()->create([
            'league_id' => $leagueSeason->league_id,
            'user_id' => $member->id,
        ]);

        $phase = AuctionRolePhase::factory()->active()->create([
            'auction_id' => $auction->id,
            'position' => 1,
        ]);

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'auction_role_phase_id' => $phase->id,
            'opening_price' => 1,
            'expires_at' => now()->addMinute(),
        ]);

        Sanctum::actingAs($member);

        $response = $this->postJson(
            "/api/auctions/{$auction->ulid}/nominations/{$nomination->ulid}/confirm"
        );

        $response->assertForbidden();

        $nomination->refresh();

        $this->assertNull($nomination->closed_at);
    }

    public function test_nomination_from_different_auction_returns_not_found(): void
    {
        $auction = Auction::factory()->live()->create();

        $leagueSeason = $auction
            ->marketSession
            ->leagueSeason;

        $president = User::factory()->create();

        LeagueMembership::factory()
            ->president()
            ->create([
                'league_id' => $leagueSeason->league_id,
                'user_id' => $president->id,
            ]);

        $otherAuction = Auction::factory()->live()->create();

        $otherPhase = AuctionRolePhase::factory()->active()->create([
            'auction_id' => $otherAuction->id,
            'position' => 1,
        ]);

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $otherAuction->id,
            'auction_role_phase_id' => $otherPhase->id,
            'opening_price' => 1,
            'expires_at' => now()->addMinute(),
        ]);

        Sanctum::actingAs($president);

        $response = $this->postJson(
            "/api/auctions/{$auction->ulid}/nominations/{$nomination->ulid}/confirm"
        );

        $response->assertNotFound();

        $nomination->refresh();

        $this->assertNull($nomination->closed_at);
    }

    public function test_completed_nomination_cannot_be_confirmed_again(): void
    {
        $auction = Auction::factory()->live()->create();

        $leagueSeason = $auction
            ->marketSession
            ->leagueSeason;

        $president = User::factory()->create();

        LeagueMembership::factory()
            ->president()
            ->create([
                'league_id' => $leagueSeason->league_id,
                'user_id' => $president->id,
            ]);

        $phase = AuctionRolePhase::factory()->active()->create([
            'auction_id' => $auction->id,
            'position' => 1,
        ]);

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'auction_role_phase_id' => $phase->id,
            'status' => AuctionNominationStatus::COMPLETED,
            'opening_price' => 1,
            'expires_at' => now()->subMinute(),
            'closed_at' => now(),
        ]);

        Sanctum::actingAs($president);

        $response = $this->postJson(
            "/api/auctions/{$auction->ulid}/nominations/{$nomination->ulid}/confirm"
        );

        $response
            ->assertUnprocessable()
            ->assertJsonPath(
                'message',
                'Auction nomination is not active.'
            );
    }

    public function test_president_of_another_league_cannot_confirm_nomination(): void
    {
        $auction = Auction::factory()->live()->create();

        $leagueSeason = $auction
            ->marketSession
            ->leagueSeason;

        $otherLeague = League::factory()->create();

        $otherPresident = User::factory()->create();

        LeagueMembership::factory()
            ->president()
            ->create([
                'league_id' => $otherLeague->id,
                'user_id' => $otherPresident->id,
            ]);

        $phase = AuctionRolePhase::factory()->active()->create([
            'auction_id' => $auction->id,
            'position' => 1,
        ]);

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'auction_role_phase_id' => $phase->id,
            'opening_price' => 1,
            'expires_at' => now()->addMinute(),
        ]);

        Sanctum::actingAs($otherPresident);

        $response = $this->postJson(
            "/api/auctions/{$auction->ulid}/nominations/{$nomination->ulid}/confirm"
        );

        $response->assertForbidden();

        $nomination->refresh();

        $this->assertNull($nomination->closed_at);
    }

    public function test_confirming_nomination_with_bid_acquires_player_and_deducts_credits(): void
    {
        $auction = Auction::factory()->live()->create();

        $leagueSeason = $auction
            ->marketSession
            ->leagueSeason;

        $president = User::factory()->create();

        LeagueMembership::factory()
            ->president()
            ->create([
                'league_id' => $leagueSeason->league_id,
                'user_id' => $president->id,
            ]);

        $participantUser = User::factory()->create();

        $membership = LeagueMembership::factory()->create([
            'league_id' => $leagueSeason->league_id,
            'user_id' => $participantUser->id,
        ]);

        $seasonParticipation = SeasonParticipation::factory()->create([
            'league_season_id' => $leagueSeason->id,
            'league_membership_id' => $membership->id,
        ]);

        $team = Team::factory()->create([
            'season_participation_id' => $seasonParticipation->id,
        ]);

        $creditAccount = TeamCreditAccount::factory()->create([
            'team_id' => $team->id,
            'current_balance' => 500,
        ]);

        $participant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'team_id' => $team->id,
            'nomination_position' => 1,
        ]);

        $phase = AuctionRolePhase::factory()->active()->create([
            'auction_id' => $auction->id,
            'position' => 1,
        ]);

        LeagueSeasonRosterRule::factory()->create([
            'league_season_id' => $leagueSeason->id,
            'role' => $phase->role,
        ]);

        $playerSeason = PlayerSeason::factory()->create([
            'role' => $phase->role,
        ]);

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'auction_role_phase_id' => $phase->id,
            'auction_participant_id' => $participant->id,
            'player_season_id' => $playerSeason->id,
            'opening_price' => 1,
            'expires_at' => now()->addMinute(),
        ]);

        AuctionBid::factory()->create([
            'auction_nomination_id' => $nomination->id,
            'auction_participant_id' => $participant->id,
            'amount' => 25,
            'sequence_number' => 1,
            'placed_at' => now(),
        ]);

        Sanctum::actingAs($president);

        $response = $this->postJson(
            "/api/auctions/{$auction->ulid}/nominations/{$nomination->ulid}/confirm"
        );

        $response->assertOk();

        $this->assertDatabaseHas('roster_ownerships', [
            'league_season_id' => $leagueSeason->id,
            'team_id' => $team->id,
            'player_season_id' => $nomination->player_season_id,
        ]);

        $creditAccount->refresh();

        $this->assertSame(475, $creditAccount->current_balance);
    }

    public function test_confirming_nomination_without_bids_does_not_acquire_player(): void
    {
        $auction = Auction::factory()->live()->create();

        $leagueSeason = $auction
            ->marketSession
            ->leagueSeason;

        $president = User::factory()->create();

        LeagueMembership::factory()
            ->president()
            ->create([
                'league_id' => $leagueSeason->league_id,
                'user_id' => $president->id,
            ]);

        $participantUser = User::factory()->create();

        $membership = LeagueMembership::factory()->create([
            'league_id' => $leagueSeason->league_id,
            'user_id' => $participantUser->id,
        ]);

        $seasonParticipation = SeasonParticipation::factory()->create([
            'league_season_id' => $leagueSeason->id,
            'league_membership_id' => $membership->id,
        ]);

        $team = Team::factory()->create([
            'season_participation_id' => $seasonParticipation->id,
        ]);

        $creditAccount = TeamCreditAccount::factory()->create([
            'team_id' => $team->id,
            'current_balance' => 500,
        ]);

        $participant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'team_id' => $team->id,
            'nomination_position' => 1,
        ]);

        $phase = AuctionRolePhase::factory()->active()->create([
            'auction_id' => $auction->id,
            'position' => 1,
        ]);

        LeagueSeasonRosterRule::factory()->create([
            'league_season_id' => $leagueSeason->id,
            'role' => $phase->role,
        ]);

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'auction_role_phase_id' => $phase->id,
            'auction_participant_id' => $participant->id,
            'opening_price' => 1,
            'expires_at' => now()->addMinute(),
        ]);

        Sanctum::actingAs($president);

        $response = $this->postJson(
            "/api/auctions/{$auction->ulid}/nominations/{$nomination->ulid}/confirm"
        );

        $response
            ->assertOk()
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath(
                'data.close_reason',
                'president_confirmed'
            );

        $this->assertDatabaseMissing('roster_ownerships', [
            'league_season_id' => $leagueSeason->id,
            'team_id' => $team->id,
            'player_season_id' => $nomination->player_season_id,
        ]);

        $creditAccount->refresh();

        $this->assertSame(500, $creditAccount->current_balance);
    }
}
