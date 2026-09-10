<?php

namespace Tests\Feature\Auction\Api;

use App\Domain\Football\Enums\PlayerRole;
use App\Models\Auction\Auction;
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

class StartAuctionNominationTest extends TestCase
{
    use RefreshDatabase;

    public function test_current_participant_can_start_nomination(): void
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
        ]);

        AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'team_id' => $team->id,
            'nomination_position' => 1,
        ]);

        AuctionRolePhase::factory()->active()->create([
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

        Sanctum::actingAs($user);

        $response = $this->postJson(
            "/api/auctions/{$auction->ulid}/nominations",
            [
                'player_season_ulid' => $playerSeason->ulid,
            ]
        );

        $response
            ->assertOk()
            ->assertJsonPath(
                'data.player.player_season_ulid',
                $playerSeason->ulid
            );

        $this->assertDatabaseHas('auction_nominations', [
            'auction_id' => $auction->id,
            'auction_participant_id' => $team
                ->auctionParticipations()
                ->first()
                ->id,
            'player_season_id' => $playerSeason->id,
            'turn_number' => 1,
        ]);
    }

    public function test_other_league_member_cannot_start_nomination_for_current_participant(): void
    {
        $auction = Auction::factory()->live()->create();

        $leagueSeason = $auction
            ->marketSession
            ->leagueSeason;

        $currentUser = User::factory()->create();

        $currentMembership = LeagueMembership::factory()->create([
            'league_id' => $leagueSeason->league_id,
            'user_id' => $currentUser->id,
        ]);

        $currentParticipation = SeasonParticipation::factory()->create([
            'league_season_id' => $leagueSeason->id,
            'league_membership_id' => $currentMembership->id,
        ]);

        $currentTeam = Team::factory()->create([
            'season_participation_id' => $currentParticipation->id,
        ]);

        TeamCreditAccount::factory()->create([
            'team_id' => $currentTeam->id,
        ]);

        AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'team_id' => $currentTeam->id,
            'nomination_position' => 1,
        ]);

        $otherUser = User::factory()->create();

        LeagueMembership::factory()->create([
            'league_id' => $leagueSeason->league_id,
            'user_id' => $otherUser->id,
        ]);

        AuctionRolePhase::factory()->active()->create([
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

        Sanctum::actingAs($otherUser);

        $response = $this->postJson(
            "/api/auctions/{$auction->ulid}/nominations",
            [
                'player_season_ulid' => $playerSeason->ulid,
            ]
        );

        $response
            ->assertStatus(422)
            ->assertJsonPath(
                'message',
                'It is not this user\'s auction turn.'
            );

        $this->assertDatabaseCount('auction_nominations', 0);
    }
}
