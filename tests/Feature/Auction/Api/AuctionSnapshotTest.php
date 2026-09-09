<?php

namespace Tests\Feature\Auction\Api;

use App\Domain\Auction\Enums\AuctionStatus;
use App\Domain\Football\Enums\PlayerRole;
use App\Models\Auction\Auction;
use App\Models\Auction\AuctionBid;
use App\Models\Auction\AuctionNomination;
use App\Models\Auction\AuctionParticipant;
use App\Models\Auction\AuctionRolePhase;
use App\Models\Credit\TeamCreditAccount;
use App\Models\Football\FootballPlayer;
use App\Models\Football\PlayerSeason;
use App\Models\Football\RealClub;
use App\Models\League\League;
use App\Models\League\LeagueMembership;
use App\Models\Market\MarketSession;
use App\Models\Season\LeagueSeason;
use App\Models\Team\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuctionSnapshotTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_league_member_can_view_auction_snapshot(): void
    {
        $user = User::factory()->create();

        $league = League::factory()->create();

        $leagueSeason = LeagueSeason::factory()->create([
            'league_id' => $league->id,
        ]);

        $marketSession = MarketSession::factory()->create([
            'league_season_id' => $leagueSeason->id,
        ]);

        $auction = Auction::factory()->create([
            'market_session_id' => $marketSession->id,
            'status' => AuctionStatus::LIVE,
            'base_timer_seconds' => 60,
            'bid_extension_seconds' => 10,
        ]);

        LeagueMembership::factory()->create([
            'league_id' => $league->id,
            'user_id' => $user->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->getJson("/api/auctions/{$auction->ulid}");

        $response
            ->assertOk()
            ->assertJson([
                'data' => [
                    'ulid' => $auction->ulid,
                    'status' => AuctionStatus::LIVE->value,
                    'base_timer_seconds' => 60,
                    'bid_extension_seconds' => 10,
                ],
            ]);
    }

    public function test_unauthenticated_user_cannot_view_auction_snapshot(): void
    {
        $auction = Auction::factory()->create();

        $response = $this
            ->getJson("/api/auctions/{$auction->ulid}");

        $response->assertUnauthorized();
    }

    public function test_auction_snapshot_returns_not_found_for_unknown_ulid(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->getJson('/api/auctions/01J00000000000000000000000');

        $response->assertNotFound();
    }

    public function test_authenticated_user_outside_league_cannot_view_auction_snapshot(): void
    {
        $user = User::factory()->create();

        $league = League::factory()->create();

        $leagueSeason = LeagueSeason::factory()->create([
            'league_id' => $league->id,
        ]);

        $marketSession = MarketSession::factory()->create([
            'league_season_id' => $leagueSeason->id,
        ]);

        $auction = Auction::factory()->create([
            'market_session_id' => $marketSession->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->getJson("/api/auctions/{$auction->ulid}");

        $response->assertForbidden();
    }

    public function test_auction_snapshot_includes_active_role_phase(): void
    {
        $user = User::factory()->create();

        $league = League::factory()->create();

        $leagueSeason = LeagueSeason::factory()->create([
            'league_id' => $league->id,
        ]);

        $marketSession = MarketSession::factory()->create([
            'league_season_id' => $leagueSeason->id,
        ]);

        $auction = Auction::factory()->create([
            'market_session_id' => $marketSession->id,
            'status' => AuctionStatus::LIVE,
        ]);

        LeagueMembership::factory()->create([
            'league_id' => $league->id,
            'user_id' => $user->id,
        ]);

        $rolePhase = AuctionRolePhase::factory()
            ->active()
            ->create([
                'auction_id' => $auction->id,
                'role' => PlayerRole::GOALKEEPER,
                'position' => 1,
            ]);

        $response = $this
            ->actingAs($user)
            ->getJson("/api/auctions/{$auction->ulid}");

        $response
            ->assertOk()
            ->assertJson([
                'data' => [
                    'active_role_phase' => [
                        'ulid' => $rolePhase->ulid,
                        'role' => PlayerRole::GOALKEEPER->value,
                        'position' => 1,
                        'status' => $rolePhase->status->value,
                    ],
                ],
            ]);
    }

    public function test_auction_snapshot_has_null_active_role_phase_when_none_is_active(): void
    {
        $user = User::factory()->create();

        $league = League::factory()->create();

        $leagueSeason = LeagueSeason::factory()->create([
            'league_id' => $league->id,
        ]);

        $marketSession = MarketSession::factory()->create([
            'league_season_id' => $leagueSeason->id,
        ]);

        $auction = Auction::factory()->create([
            'market_session_id' => $marketSession->id,
            'status' => AuctionStatus::LIVE,
        ]);

        LeagueMembership::factory()->create([
            'league_id' => $league->id,
            'user_id' => $user->id,
        ]);

        AuctionRolePhase::factory()->create([
            'auction_id' => $auction->id,
            'role' => PlayerRole::GOALKEEPER,
            'position' => 1,
        ]);

        $response = $this
            ->actingAs($user)
            ->getJson("/api/auctions/{$auction->ulid}");

        $response
            ->assertOk()
            ->assertJsonPath('data.active_role_phase', null);
    }

    public function test_auction_snapshot_includes_active_nomination(): void
    {
        $user = User::factory()->create();

        $league = League::factory()->create();

        $leagueSeason = LeagueSeason::factory()->create([
            'league_id' => $league->id,
        ]);

        $marketSession = MarketSession::factory()->create([
            'league_season_id' => $leagueSeason->id,
        ]);

        $auction = Auction::factory()->create([
            'market_session_id' => $marketSession->id,
            'status' => AuctionStatus::LIVE,
        ]);

        LeagueMembership::factory()->create([
            'league_id' => $league->id,
            'user_id' => $user->id,
        ]);

        $rolePhase = AuctionRolePhase::factory()
            ->active()
            ->create([
                'auction_id' => $auction->id,
                'role' => PlayerRole::GOALKEEPER,
                'position' => 1,
            ]);

        $team = Team::factory()->create();

        TeamCreditAccount::factory()->create([
            'team_id' => $team->id,
            'initial_balance' => 500,
            'current_balance' => 500,
        ]);

        $participant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'team_id' => $team->id,
        ]);

        $playerSeason = PlayerSeason::factory()->create();

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'auction_role_phase_id' => $rolePhase->id,
            'auction_participant_id' => $participant->id,
            'player_season_id' => $playerSeason->id,
            'turn_number' => 1,
            'opening_price' => 1,
        ]);

        $response = $this
            ->actingAs($user)
            ->getJson("/api/auctions/{$auction->ulid}");

        $response
            ->assertOk()
            ->assertJson([
                'data' => [
                    'active_nomination' => [
                        'ulid' => $nomination->ulid,
                        'player_season_ulid' => $playerSeason->ulid,
                        'turn_number' => 1,
                        'opening_price' => 1,
                        'status' => $nomination->status->value,
                        'timer_started_at' => $nomination->timer_started_at->toISOString(),
                        'expires_at' => $nomination->expires_at->toISOString(),
                    ],
                ],
            ]);
    }

    public function test_auction_snapshot_has_null_active_nomination_when_none_is_active(): void
    {
        $user = User::factory()->create();

        $league = League::factory()->create();

        $leagueSeason = LeagueSeason::factory()->create([
            'league_id' => $league->id,
        ]);

        $marketSession = MarketSession::factory()->create([
            'league_season_id' => $leagueSeason->id,
        ]);

        $auction = Auction::factory()->create([
            'market_session_id' => $marketSession->id,
            'status' => AuctionStatus::LIVE,
        ]);

        LeagueMembership::factory()->create([
            'league_id' => $league->id,
            'user_id' => $user->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->getJson("/api/auctions/{$auction->ulid}");

        $response
            ->assertOk()
            ->assertJsonPath('data.active_nomination', null);
    }

    public function test_auction_snapshot_includes_current_bid_for_active_nomination(): void
    {
        $user = User::factory()->create();

        $league = League::factory()->create();

        $leagueSeason = LeagueSeason::factory()->create([
            'league_id' => $league->id,
        ]);

        $marketSession = MarketSession::factory()->create([
            'league_season_id' => $leagueSeason->id,
        ]);

        $auction = Auction::factory()->create([
            'market_session_id' => $marketSession->id,
            'status' => AuctionStatus::LIVE,
        ]);

        LeagueMembership::factory()->create([
            'league_id' => $league->id,
            'user_id' => $user->id,
        ]);

        $rolePhase = AuctionRolePhase::factory()
            ->active()
            ->create([
                'auction_id' => $auction->id,
                'role' => PlayerRole::GOALKEEPER,
                'position' => 1,
            ]);

        $team = Team::factory()->create();

        TeamCreditAccount::factory()->create([
            'team_id' => $team->id,
            'initial_balance' => 500,
            'current_balance' => 500,
        ]);

        $participant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'team_id' => $team->id,
        ]);

        $playerSeason = PlayerSeason::factory()->create();

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'auction_role_phase_id' => $rolePhase->id,
            'auction_participant_id' => $participant->id,
            'player_season_id' => $playerSeason->id,
        ]);

        AuctionBid::factory()->create([
            'auction_nomination_id' => $nomination->id,
            'auction_participant_id' => $participant->id,
            'amount' => 10,
            'sequence_number' => 1,
        ]);

        $currentBid = AuctionBid::factory()->create([
            'auction_nomination_id' => $nomination->id,
            'auction_participant_id' => $participant->id,
            'amount' => 15,
            'sequence_number' => 2,
        ]);

        $response = $this
            ->actingAs($user)
            ->getJson("/api/auctions/{$auction->ulid}");

        $response
            ->assertOk()
            ->assertJson([
                'data' => [
                    'active_nomination' => [
                        'current_bid' => [
                            'ulid' => $currentBid->ulid,
                            'amount' => 15,
                            'sequence_number' => 2,
                            'participant_ulid' => $participant->ulid,
                            'placed_at' => $currentBid->placed_at->toISOString(),
                        ],
                    ],
                ],
            ]);
    }

    public function test_auction_snapshot_has_null_current_bid_when_active_nomination_has_no_bids(): void
    {
        $user = User::factory()->create();

        $league = League::factory()->create();

        $leagueSeason = LeagueSeason::factory()->create([
            'league_id' => $league->id,
        ]);

        $marketSession = MarketSession::factory()->create([
            'league_season_id' => $leagueSeason->id,
        ]);

        $auction = Auction::factory()->create([
            'market_session_id' => $marketSession->id,
            'status' => AuctionStatus::LIVE,
        ]);

        LeagueMembership::factory()->create([
            'league_id' => $league->id,
            'user_id' => $user->id,
        ]);

        $rolePhase = AuctionRolePhase::factory()
            ->active()
            ->create([
                'auction_id' => $auction->id,
                'role' => PlayerRole::GOALKEEPER,
                'position' => 1,
            ]);

        $team = Team::factory()->create();

        TeamCreditAccount::factory()->create([
            'team_id' => $team->id,
            'initial_balance' => 500,
            'current_balance' => 500,
        ]);

        $participant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'team_id' => $team->id,
        ]);

        $playerSeason = PlayerSeason::factory()->create();

        AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'auction_role_phase_id' => $rolePhase->id,
            'auction_participant_id' => $participant->id,
            'player_season_id' => $playerSeason->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->getJson("/api/auctions/{$auction->ulid}");

        $response
            ->assertOk()
            ->assertJsonPath(
                'data.active_nomination.current_bid',
                null
            );
    }

    public function test_auction_snapshot_includes_nominator_for_active_nomination(): void
    {
        $user = User::factory()->create();

        $league = League::factory()->create();

        $leagueSeason = LeagueSeason::factory()->create([
            'league_id' => $league->id,
        ]);

        $marketSession = MarketSession::factory()->create([
            'league_season_id' => $leagueSeason->id,
        ]);

        $auction = Auction::factory()->create([
            'market_session_id' => $marketSession->id,
            'status' => AuctionStatus::LIVE,
        ]);

        LeagueMembership::factory()->create([
            'league_id' => $league->id,
            'user_id' => $user->id,
        ]);

        $rolePhase = AuctionRolePhase::factory()
            ->active()
            ->create([
                'auction_id' => $auction->id,
                'role' => PlayerRole::GOALKEEPER,
                'position' => 1,
            ]);

        $team = Team::factory()->create();

        TeamCreditAccount::factory()->create([
            'team_id' => $team->id,
            'initial_balance' => 500,
            'current_balance' => 500,
        ]);

        $participant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'nomination_position' => 3,
            'team_id' => $team->id,
        ]);

        $playerSeason = PlayerSeason::factory()->create();

        AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'auction_role_phase_id' => $rolePhase->id,
            'auction_participant_id' => $participant->id,
            'player_season_id' => $playerSeason->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->getJson("/api/auctions/{$auction->ulid}");

        $response
            ->assertOk()
            ->assertJson([
                'data' => [
                    'active_nomination' => [
                        'nominator' => [
                            'ulid' => $participant->ulid,
                            'nomination_position' => 3,
                        ],
                    ],
                ],
            ]);
    }

    public function test_auction_snapshot_includes_participants_with_team_and_current_balance(): void
    {
        $user = User::factory()->create();

        $league = League::factory()->create();

        $leagueSeason = LeagueSeason::factory()->create([
            'league_id' => $league->id,
        ]);

        $marketSession = MarketSession::factory()->create([
            'league_season_id' => $leagueSeason->id,
        ]);

        $auction = Auction::factory()->create([
            'market_session_id' => $marketSession->id,
            'status' => AuctionStatus::LIVE,
        ]);

        LeagueMembership::factory()->create([
            'league_id' => $league->id,
            'user_id' => $user->id,
        ]);

        $team = Team::factory()->create([
            'name' => 'FC Andrea',
            'short_name' => 'FCA',
        ]);

        $creditAccount = TeamCreditAccount::factory()->create([
            'team_id' => $team->id,
            'initial_balance' => 500,
            'current_balance' => 437,
        ]);

        $participant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'team_id' => $team->id,
            'nomination_position' => 2,
        ]);

        $response = $this
            ->actingAs($user)
            ->getJson("/api/auctions/{$auction->ulid}");

        $response
            ->assertOk()
            ->assertJson([
                'data' => [
                    'participants' => [
                        [
                            'ulid' => $participant->ulid,
                            'nomination_position' => 2,
                            'team' => [
                                'ulid' => $team->ulid,
                                'name' => 'FC Andrea',
                                'short_name' => 'FCA',
                                'current_balance' => $creditAccount->current_balance,
                            ],
                        ],
                    ],
                ],
            ]);
    }

    public function test_auction_snapshot_orders_participants_by_nomination_position(): void
    {
        $user = User::factory()->create();

        $league = League::factory()->create();

        $leagueSeason = LeagueSeason::factory()->create([
            'league_id' => $league->id,
        ]);

        $marketSession = MarketSession::factory()->create([
            'league_season_id' => $leagueSeason->id,
        ]);

        $auction = Auction::factory()->create([
            'market_session_id' => $marketSession->id,
            'status' => AuctionStatus::LIVE,
        ]);

        LeagueMembership::factory()->create([
            'league_id' => $league->id,
            'user_id' => $user->id,
        ]);

        $teamThree = Team::factory()->create();
        $teamOne = Team::factory()->create();
        $teamTwo = Team::factory()->create();

        TeamCreditAccount::factory()->create([
            'team_id' => $teamThree->id,
        ]);

        TeamCreditAccount::factory()->create([
            'team_id' => $teamOne->id,
        ]);

        TeamCreditAccount::factory()->create([
            'team_id' => $teamTwo->id,
        ]);

        $participantThree = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'team_id' => $teamThree->id,
            'nomination_position' => 3,
        ]);

        $participantOne = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'team_id' => $teamOne->id,
            'nomination_position' => 1,
        ]);

        $participantTwo = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'team_id' => $teamTwo->id,
            'nomination_position' => 2,
        ]);

        $response = $this
            ->actingAs($user)
            ->getJson("/api/auctions/{$auction->ulid}");

        $response
            ->assertOk()
            ->assertJsonPath('data.participants.0.ulid', $participantOne->ulid)
            ->assertJsonPath('data.participants.1.ulid', $participantTwo->ulid)
            ->assertJsonPath('data.participants.2.ulid', $participantThree->ulid);
    }

    public function test_auction_snapshot_includes_player_details_for_active_nomination(): void
    {
        $user = User::factory()->create();

        $league = League::factory()->create();

        $leagueSeason = LeagueSeason::factory()->create([
            'league_id' => $league->id,
        ]);

        $marketSession = MarketSession::factory()->create([
            'league_season_id' => $leagueSeason->id,
        ]);

        $auction = Auction::factory()->create([
            'market_session_id' => $marketSession->id,
            'status' => AuctionStatus::LIVE,
        ]);

        LeagueMembership::factory()->create([
            'league_id' => $league->id,
            'user_id' => $user->id,
        ]);

        $rolePhase = AuctionRolePhase::factory()
            ->active()
            ->create([
                'auction_id' => $auction->id,
                'role' => PlayerRole::GOALKEEPER,
                'position' => 1,
            ]);

        $team = Team::factory()->create();

        TeamCreditAccount::factory()->create([
            'team_id' => $team->id,
            'initial_balance' => 500,
            'current_balance' => 500,
        ]);

        $participant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'team_id' => $team->id,
        ]);

        $footballPlayer = FootballPlayer::factory()->create([
            'display_name' => 'Maignan',
        ]);

        $realClub = RealClub::factory()->create([
            'name' => 'Milan',
            'short_name' => 'MIL',
        ]);

        $playerSeason = PlayerSeason::factory()->create([
            'football_player_id' => $footballPlayer->id,
            'real_club_id' => $realClub->id,
            'role' => PlayerRole::GOALKEEPER,
        ]);

        AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'auction_role_phase_id' => $rolePhase->id,
            'auction_participant_id' => $participant->id,
            'player_season_id' => $playerSeason->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->getJson("/api/auctions/{$auction->ulid}");

        $response
            ->assertOk()
            ->assertJson([
                'data' => [
                    'active_nomination' => [
                        'player' => [
                            'player_season_ulid' => $playerSeason->ulid,
                            'football_player_ulid' => $footballPlayer->ulid,
                            'display_name' => 'Maignan',
                            'role' => PlayerRole::GOALKEEPER->value,
                            'real_club' => [
                                'ulid' => $realClub->ulid,
                                'name' => 'Milan',
                                'short_name' => 'MIL',
                            ],
                        ],
                    ],
                ],
            ]);
    }
}
