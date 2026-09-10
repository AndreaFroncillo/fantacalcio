<?php

namespace Tests\Feature\Auction\Api;

use App\Domain\Auction\Enums\AuctionNominationStatus;
use App\Models\Auction\Auction;
use App\Models\Auction\AuctionBid;
use App\Models\Auction\AuctionNomination;
use App\Models\Auction\AuctionParticipant;
use App\Models\Auction\AuctionRolePhase;
use App\Models\Credit\TeamCreditAccount;
use App\Models\Football\PlayerSeason;
use App\Models\League\LeagueMembership;
use App\Models\Roster\LeagueSeasonRosterRule;
use App\Models\Season\SeasonParticipation;
use App\Models\Team\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RejectAuctionNominationTest extends TestCase
{
    use RefreshDatabase;

    public function test_league_president_can_reject_active_nomination(): void
    {
        $auction = Auction::factory()->live()->create();

        $leagueSeason = $auction
            ->marketSession
            ->leagueSeason;

        $president = User::factory()->create();

        $seasonParticipation = SeasonParticipation::factory()->create([
            'league_season_id' => $leagueSeason->id,
            'league_membership_id' => LeagueMembership::factory()->create([
                'league_id' => $leagueSeason->league_id,
            ])->id,
        ]);

        $team = Team::factory()->create([
            'season_participation_id' => $seasonParticipation->id,
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
            "/api/auctions/{$auction->ulid}/nominations/{$nomination->ulid}/reject"
        );

        $response
            ->assertOk()
            ->assertJsonPath('data.ulid', $nomination->ulid)
            ->assertJsonPath('data.status', 'rejected')
            ->assertJsonPath(
                'data.close_reason',
                'president_rejected'
            );

        $nomination->refresh();

        $this->assertSame(
            AuctionNominationStatus::REJECTED,
            $nomination->status
        );

        $this->assertNotNull($nomination->closed_at);
        $this->assertSame(
            $president->id,
            $nomination->closed_by_user_id
        );
    }

    public function test_normal_league_member_cannot_reject_nomination(): void
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
            'expires_at' => now()->addMinute(),
        ]);

        Sanctum::actingAs($member);

        $response = $this->postJson(
            "/api/auctions/{$auction->ulid}/nominations/{$nomination->ulid}/reject"
        );

        $response->assertForbidden();

        $this->assertNull(
            $nomination->fresh()->closed_at
        );
    }

    public function test_president_of_another_league_cannot_reject_nomination(): void
    {
        $auction = Auction::factory()->live()->create();

        $president = User::factory()->create();

        LeagueMembership::factory()
            ->president()
            ->create([
                'user_id' => $president->id,
            ]);

        $phase = AuctionRolePhase::factory()->active()->create([
            'auction_id' => $auction->id,
            'position' => 1,
        ]);

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'auction_role_phase_id' => $phase->id,
            'expires_at' => now()->addMinute(),
        ]);

        Sanctum::actingAs($president);

        $response = $this->postJson(
            "/api/auctions/{$auction->ulid}/nominations/{$nomination->ulid}/reject"
        );

        $response->assertForbidden();

        $this->assertNull(
            $nomination->fresh()->closed_at
        );
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
            'expires_at' => now()->addMinute(),
        ]);

        Sanctum::actingAs($president);

        $response = $this->postJson(
            "/api/auctions/{$auction->ulid}/nominations/{$nomination->ulid}/reject"
        );

        $response->assertNotFound();

        $this->assertNull(
            $nomination->fresh()->closed_at
        );
    }

    public function test_completed_nomination_cannot_be_rejected_again(): void
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
            'closed_at' => now(),
            'expires_at' => now()->addMinute(),
        ]);

        Sanctum::actingAs($president);

        $response = $this->postJson(
            "/api/auctions/{$auction->ulid}/nominations/{$nomination->ulid}/reject"
        );

        $response
            ->assertUnprocessable()
            ->assertJsonPath(
                'message',
                'Auction nomination is not active.'
            );
    }

    public function test_rejecting_nomination_with_bid_does_not_acquire_player_or_deduct_credits(): void
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

        LeagueSeasonRosterRule::factory()->create([
            'league_season_id' => $leagueSeason->id,
            'role' => $phase->role,
        ]);

        $seasonParticipation = SeasonParticipation::factory()->create([
            'league_season_id' => $leagueSeason->id,
            'league_membership_id' => LeagueMembership::factory()->create([
                'league_id' => $leagueSeason->league_id,
            ])->id,
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
            'amount' => 50,
            'sequence_number' => 1,
        ]);

        Sanctum::actingAs($president);

        $response = $this->postJson(
            "/api/auctions/{$auction->ulid}/nominations/{$nomination->ulid}/reject"
        );

        $response
            ->assertOk()
            ->assertJsonPath('data.status', 'rejected')
            ->assertJsonPath(
                'data.close_reason',
                'president_rejected'
            );

        $this->assertDatabaseMissing('roster_ownerships', [
            'league_season_id' => $leagueSeason->id,
            'player_season_id' => $playerSeason->id,
        ]);

        $this->assertSame(
            500,
            $creditAccount->fresh()->current_balance
        );
    }

    public function test_rejecting_nomination_without_bids_does_not_acquire_player(): void
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

        LeagueSeasonRosterRule::factory()->create([
            'league_season_id' => $leagueSeason->id,
            'role' => $phase->role,
        ]);

        $seasonParticipation = SeasonParticipation::factory()->create([
            'league_season_id' => $leagueSeason->id,
            'league_membership_id' => LeagueMembership::factory()->create([
                'league_id' => $leagueSeason->league_id,
            ])->id,
        ]);

        $team = Team::factory()->create([
            'season_participation_id' => $seasonParticipation->id,
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

        Sanctum::actingAs($president);

        $response = $this->postJson(
            "/api/auctions/{$auction->ulid}/nominations/{$nomination->ulid}/reject"
        );

        $response
            ->assertOk()
            ->assertJsonPath('data.status', 'rejected')
            ->assertJsonPath(
                'data.close_reason',
                'president_rejected'
            );

        $this->assertDatabaseMissing('roster_ownerships', [
            'league_season_id' => $leagueSeason->id,
            'player_season_id' => $playerSeason->id,
        ]);
    }
}
