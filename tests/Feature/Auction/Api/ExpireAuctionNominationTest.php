<?php

namespace Tests\Feature\Auction\Api;

use App\Domain\Auction\Enums\AuctionNominationStatus;
use App\Domain\Football\Enums\PlayerRole;
use App\Models\Auction\Auction;
use App\Models\Auction\AuctionBid;
use App\Models\Auction\AuctionNomination;
use App\Models\Auction\AuctionParticipant;
use App\Models\Auction\AuctionRolePhase;
use App\Models\Credit\TeamCreditAccount;
use App\Models\Football\FootballSeason;
use App\Models\Football\PlayerSeason;
use App\Models\League\LeagueMembership;
use App\Models\Roster\LeagueSeasonRosterRule;
use App\Models\Season\SeasonParticipation;
use App\Models\Team\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ExpireAuctionNominationTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_league_member_can_expire_elapsed_nomination(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        $leagueSeason = $auction
            ->marketSession
            ->leagueSeason;

        $member = User::factory()->create();

        LeagueMembership::factory()->create([
            'league_id' => $leagueSeason->league_id,
            'user_id' => $member->id,
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
            'current_balance' => 100,
        ]);

        $participant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'team_id' => $team->id,
            'nomination_position' => 1,
        ]);

        $phase = AuctionRolePhase::factory()
            ->active()
            ->create([
                'auction_id' => $auction->id,
                'role' => PlayerRole::GOALKEEPER,
                'position' => 1,
            ]);

        LeagueSeasonRosterRule::factory()->create([
            'league_season_id' => $leagueSeason->id,
            'role' => PlayerRole::GOALKEEPER,
            'max_players' => 3,
        ]);

        $footballSeason = FootballSeason::factory()->create([
            'start_year' => $leagueSeason->start_year,
            'end_year' => $leagueSeason->end_year,
        ]);

        $playerSeason = PlayerSeason::factory()->create([
            'football_season_id' => $footballSeason->id,
            'role' => PlayerRole::GOALKEEPER,
        ]);

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'auction_role_phase_id' => $phase->id,
            'auction_participant_id' => $participant->id,
            'player_season_id' => $playerSeason->id,
            'status' => AuctionNominationStatus::ACTIVE,
            'turn_number' => 1,
            'opening_price' => 1,
            'timer_started_at' => now()->subMinutes(2),
            'expires_at' => now()->subMinute(),
        ]);

        AuctionBid::factory()->create([
            'auction_nomination_id' => $nomination->id,
            'auction_participant_id' => $participant->id,
            'amount' => 10,
            'sequence_number' => 1,
        ]);

        Sanctum::actingAs($member);

        $response = $this->postJson(
            "/api/auctions/{$auction->ulid}/nominations/{$nomination->ulid}/expire"
        );

        $response
            ->assertOk()
            ->assertJsonPath('data.ulid', $nomination->ulid)
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath(
                'data.close_reason',
                'timer_expired'
            );

        $this->assertSame(
            90,
            $creditAccount->fresh()->current_balance
        );

        $this->assertDatabaseHas('roster_ownerships', [
            'league_season_id' => $leagueSeason->id,
            'team_id' => $team->id,
            'player_season_id' => $playerSeason->id,
            'acquisition_value' => 10,
        ]);
    }

    public function test_active_league_member_cannot_expire_nomination_before_timer_ends(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        $leagueSeason = $auction
            ->marketSession
            ->leagueSeason;

        $member = User::factory()->create();

        LeagueMembership::factory()->create([
            'league_id' => $leagueSeason->league_id,
            'user_id' => $member->id,
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
            'current_balance' => 100,
        ]);

        $participant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'team_id' => $team->id,
            'nomination_position' => 1,
        ]);

        $phase = AuctionRolePhase::factory()
            ->active()
            ->create([
                'auction_id' => $auction->id,
                'role' => PlayerRole::GOALKEEPER,
                'position' => 1,
            ]);

        LeagueSeasonRosterRule::factory()->create([
            'league_season_id' => $leagueSeason->id,
            'role' => PlayerRole::GOALKEEPER,
            'max_players' => 3,
        ]);

        $footballSeason = FootballSeason::factory()->create([
            'start_year' => $leagueSeason->start_year,
            'end_year' => $leagueSeason->end_year,
        ]);

        $playerSeason = PlayerSeason::factory()->create([
            'football_season_id' => $footballSeason->id,
            'role' => PlayerRole::GOALKEEPER,
        ]);

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'auction_role_phase_id' => $phase->id,
            'auction_participant_id' => $participant->id,
            'player_season_id' => $playerSeason->id,
            'status' => AuctionNominationStatus::ACTIVE,
            'turn_number' => 1,
            'opening_price' => 1,
            'timer_started_at' => now(),
            'expires_at' => now()->addMinute(),
        ]);

        AuctionBid::factory()->create([
            'auction_nomination_id' => $nomination->id,
            'auction_participant_id' => $participant->id,
            'amount' => 10,
            'sequence_number' => 1,
        ]);

        Sanctum::actingAs($member);

        $response = $this->postJson(
            "/api/auctions/{$auction->ulid}/nominations/{$nomination->ulid}/expire"
        );

        $response
            ->assertUnprocessable()
            ->assertJsonPath(
                'message',
                'Auction nomination timer has not expired.'
            );

        $this->assertSame(
            AuctionNominationStatus::ACTIVE,
            $nomination->fresh()->status
        );

        $this->assertNull(
            $nomination->fresh()->close_reason
        );

        $this->assertSame(
            100,
            $creditAccount->fresh()->current_balance
        );

        $this->assertDatabaseMissing('roster_ownerships', [
            'league_season_id' => $leagueSeason->id,
            'player_season_id' => $playerSeason->id,
        ]);
    }

    public function test_user_outside_league_cannot_expire_nomination(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        $outsider = User::factory()->create();

        $phase = AuctionRolePhase::factory()
            ->active()
            ->create([
                'auction_id' => $auction->id,
                'role' => PlayerRole::GOALKEEPER,
                'position' => 1,
            ]);

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'auction_role_phase_id' => $phase->id,
            'status' => AuctionNominationStatus::ACTIVE,
            'expires_at' => now()->subMinute(),
        ]);

        Sanctum::actingAs($outsider);

        $response = $this->postJson(
            "/api/auctions/{$auction->ulid}/nominations/{$nomination->ulid}/expire"
        );

        $response->assertForbidden();

        $this->assertSame(
            AuctionNominationStatus::ACTIVE,
            $nomination->fresh()->status
        );
    }

    public function test_inactive_league_member_cannot_expire_nomination(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        $leagueSeason = $auction
            ->marketSession
            ->leagueSeason;

        $member = User::factory()->create();

        LeagueMembership::factory()
            ->inactive()
            ->create([
                'league_id' => $leagueSeason->league_id,
                'user_id' => $member->id,
            ]);

        $phase = AuctionRolePhase::factory()
            ->active()
            ->create([
                'auction_id' => $auction->id,
                'role' => PlayerRole::GOALKEEPER,
                'position' => 1,
            ]);

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'auction_role_phase_id' => $phase->id,
            'status' => AuctionNominationStatus::ACTIVE,
            'expires_at' => now()->subMinute(),
        ]);

        Sanctum::actingAs($member);

        $response = $this->postJson(
            "/api/auctions/{$auction->ulid}/nominations/{$nomination->ulid}/expire"
        );

        $response->assertForbidden();

        $this->assertSame(
            AuctionNominationStatus::ACTIVE,
            $nomination->fresh()->status
        );
    }

    public function test_nomination_from_different_auction_returns_not_found(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        $otherAuction = Auction::factory()
            ->live()
            ->create();

        $leagueSeason = $auction
            ->marketSession
            ->leagueSeason;

        $member = User::factory()->create();

        LeagueMembership::factory()->create([
            'league_id' => $leagueSeason->league_id,
            'user_id' => $member->id,
        ]);

        $otherPhase = AuctionRolePhase::factory()
            ->active()
            ->create([
                'auction_id' => $otherAuction->id,
                'role' => PlayerRole::GOALKEEPER,
                'position' => 1,
            ]);

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $otherAuction->id,
            'auction_role_phase_id' => $otherPhase->id,
            'status' => AuctionNominationStatus::ACTIVE,
            'expires_at' => now()->subMinute(),
        ]);

        Sanctum::actingAs($member);

        $response = $this->postJson(
            "/api/auctions/{$auction->ulid}/nominations/{$nomination->ulid}/expire"
        );

        $response->assertNotFound();

        $this->assertSame(
            AuctionNominationStatus::ACTIVE,
            $nomination->fresh()->status
        );
    }

    public function test_already_closed_nomination_cannot_expire_again(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        $leagueSeason = $auction
            ->marketSession
            ->leagueSeason;

        $member = User::factory()->create();

        LeagueMembership::factory()->create([
            'league_id' => $leagueSeason->league_id,
            'user_id' => $member->id,
        ]);

        $phase = AuctionRolePhase::factory()
            ->active()
            ->create([
                'auction_id' => $auction->id,
                'role' => PlayerRole::GOALKEEPER,
                'position' => 1,
            ]);

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'auction_role_phase_id' => $phase->id,
            'status' => AuctionNominationStatus::COMPLETED,
            'expires_at' => now()->subMinute(),
            'closed_at' => now()->subMinute(),
        ]);

        Sanctum::actingAs($member);

        $response = $this->postJson(
            "/api/auctions/{$auction->ulid}/nominations/{$nomination->ulid}/expire"
        );

        $response
            ->assertUnprocessable()
            ->assertJsonPath(
                'message',
                'Auction nomination is not active.'
            );

        $this->assertSame(
            AuctionNominationStatus::COMPLETED,
            $nomination->fresh()->status
        );
    }
}
