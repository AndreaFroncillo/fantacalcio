<?php

namespace Tests\Feature\Auction;

use App\Domain\Auction\Actions\ExpireAuctionNomination;
use App\Domain\Auction\Enums\AuctionNominationCloseReason;
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
use App\Models\Roster\LeagueSeasonRosterRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpireAuctionNominationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_expires_nomination_and_acquires_player_for_highest_bid(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        $rolePhase = AuctionRolePhase::factory()
            ->active()
            ->create([
                'auction_id' => $auction->id,
                'role' => PlayerRole::GOALKEEPER,
                'position' => 1,
            ]);

        $participant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'nomination_position' => 1,
        ]);

        $creditAccount = TeamCreditAccount::factory()->create([
            'team_id' => $participant->team_id,
            'current_balance' => 100,
        ]);

        LeagueSeasonRosterRule::factory()->create([
            'league_season_id' => $auction
                ->marketSession
                ->league_season_id,
            'role' => PlayerRole::GOALKEEPER,
            'max_players' => 3,
        ]);

        $footballSeason = FootballSeason::factory()->create([
            'start_year' => $auction
                ->marketSession
                ->leagueSeason
                ->start_year,
            'end_year' => $auction
                ->marketSession
                ->leagueSeason
                ->end_year,
        ]);

        $playerSeason = PlayerSeason::factory()->create([
            'football_season_id' => $footballSeason->id,
            'role' => PlayerRole::GOALKEEPER,
        ]);

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'auction_role_phase_id' => $rolePhase->id,
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

        $ownership = app(ExpireAuctionNomination::class)
            ->execute($nomination);

        $this->assertNotNull($ownership);

        $this->assertSame(
            AuctionNominationStatus::COMPLETED,
            $nomination->fresh()->status
        );

        $this->assertSame(
            AuctionNominationCloseReason::TIMER_EXPIRED,
            $nomination->fresh()->close_reason
        );

        $this->assertSame(
            90,
            $creditAccount->fresh()->current_balance
        );

        $this->assertDatabaseHas('roster_ownerships', [
            'league_season_id' => $auction
                ->marketSession
                ->league_season_id,
            'team_id' => $participant->team_id,
            'player_season_id' => $playerSeason->id,
            'acquisition_value' => 10,
        ]);
    }

    public function test_it_fails_when_nomination_timer_has_not_expired(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        $rolePhase = AuctionRolePhase::factory()
            ->active()
            ->create([
                'auction_id' => $auction->id,
                'role' => PlayerRole::GOALKEEPER,
                'position' => 1,
            ]);

        $participant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'nomination_position' => 1,
        ]);

        TeamCreditAccount::factory()->create([
            'team_id' => $participant->team_id,
            'current_balance' => 100,
        ]);

        LeagueSeasonRosterRule::factory()->create([
            'league_season_id' => $auction
                ->marketSession
                ->league_season_id,
            'role' => PlayerRole::GOALKEEPER,
            'max_players' => 3,
        ]);

        $footballSeason = FootballSeason::factory()->create([
            'start_year' => $auction
                ->marketSession
                ->leagueSeason
                ->start_year,
            'end_year' => $auction
                ->marketSession
                ->leagueSeason
                ->end_year,
        ]);

        $playerSeason = PlayerSeason::factory()->create([
            'football_season_id' => $footballSeason->id,
            'role' => PlayerRole::GOALKEEPER,
        ]);

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'auction_role_phase_id' => $rolePhase->id,
            'auction_participant_id' => $participant->id,
            'player_season_id' => $playerSeason->id,
            'status' => AuctionNominationStatus::ACTIVE,
            'turn_number' => 1,
            'opening_price' => 1,
            'timer_started_at' => now(),
            'expires_at' => now()->addMinute(),
        ]);

        $this->expectException(\RuntimeException::class);

        $this->expectExceptionMessage(
            'Auction nomination timer has not expired.'
        );

        app(ExpireAuctionNomination::class)
            ->execute($nomination);
    }
}
