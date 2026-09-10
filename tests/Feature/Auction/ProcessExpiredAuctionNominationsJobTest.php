<?php

namespace Tests\Feature\Auction;

use App\Domain\Auction\Actions\ExpireAuctionNomination;
use App\Domain\Auction\Enums\AuctionNominationStatus;
use App\Domain\Football\Enums\PlayerRole;
use App\Jobs\Auction\ProcessExpiredAuctionNominationsJob;
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

class ProcessExpiredAuctionNominationsJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_expires_elapsed_nomination_and_acquires_player(): void
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

        app(ProcessExpiredAuctionNominationsJob::class)
            ->handle(
                app(ExpireAuctionNomination::class)
            );

        $this->assertSame(
            AuctionNominationStatus::COMPLETED,
            $nomination->fresh()->status
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

    public function test_job_does_nothing_when_no_nomination_has_expired(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

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
            'timer_started_at' => now(),
            'expires_at' => now()->addMinute(),
        ]);

        app(ProcessExpiredAuctionNominationsJob::class)
            ->handle(
                app(ExpireAuctionNomination::class)
            );

        $this->assertSame(
            AuctionNominationStatus::ACTIVE,
            $nomination->fresh()->status
        );

        $this->assertNull(
            $nomination->fresh()->closed_at
        );
    }

    public function test_job_ignores_expired_nomination_from_non_live_auction(): void
    {
        $auction = Auction::factory()->create();

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
            'timer_started_at' => now()->subMinutes(2),
            'expires_at' => now()->subMinute(),
        ]);

        app(ProcessExpiredAuctionNominationsJob::class)
            ->handle(
                app(ExpireAuctionNomination::class)
            );

        $this->assertSame(
            AuctionNominationStatus::ACTIVE,
            $nomination->fresh()->status
        );

        $this->assertNull(
            $nomination->fresh()->closed_at
        );
    }

    public function test_job_processes_all_expired_nominations(): void
    {
        $firstAuction = Auction::factory()
            ->live()
            ->create();

        $secondAuction = Auction::factory()
            ->live()
            ->create();

        $firstPhase = AuctionRolePhase::factory()
            ->active()
            ->create([
                'auction_id' => $firstAuction->id,
                'role' => PlayerRole::GOALKEEPER,
                'position' => 1,
            ]);

        $secondPhase = AuctionRolePhase::factory()
            ->active()
            ->create([
                'auction_id' => $secondAuction->id,
                'role' => PlayerRole::GOALKEEPER,
                'position' => 1,
            ]);

        $firstParticipant = AuctionParticipant::factory()->create([
            'auction_id' => $firstAuction->id,
            'nomination_position' => 1,
        ]);

        $secondParticipant = AuctionParticipant::factory()->create([
            'auction_id' => $secondAuction->id,
            'nomination_position' => 1,
        ]);

        TeamCreditAccount::factory()->create([
            'team_id' => $firstParticipant->team_id,
            'current_balance' => 100,
        ]);

        TeamCreditAccount::factory()->create([
            'team_id' => $secondParticipant->team_id,
            'current_balance' => 100,
        ]);

        LeagueSeasonRosterRule::factory()->create([
            'league_season_id' => $firstAuction
                ->marketSession
                ->league_season_id,
            'role' => PlayerRole::GOALKEEPER,
            'max_players' => 3,
        ]);

        LeagueSeasonRosterRule::factory()->create([
            'league_season_id' => $secondAuction
                ->marketSession
                ->league_season_id,
            'role' => PlayerRole::GOALKEEPER,
            'max_players' => 3,
        ]);

        $firstFootballSeason = FootballSeason::firstOrCreate([
            'start_year' => $firstAuction
                ->marketSession
                ->leagueSeason
                ->start_year,
            'end_year' => $firstAuction
                ->marketSession
                ->leagueSeason
                ->end_year,
        ], [
            'name' => 'Serie A Test',
        ]);

        $secondFootballSeason = FootballSeason::firstOrCreate([
            'start_year' => $secondAuction
                ->marketSession
                ->leagueSeason
                ->start_year,
            'end_year' => $secondAuction
                ->marketSession
                ->leagueSeason
                ->end_year,
        ], [
            'name' => 'Serie A Test',
        ]);

        $firstPlayerSeason = PlayerSeason::factory()->create([
            'football_season_id' => $firstFootballSeason->id,
            'role' => PlayerRole::GOALKEEPER,
        ]);

        $secondPlayerSeason = PlayerSeason::factory()->create([
            'football_season_id' => $secondFootballSeason->id,
            'role' => PlayerRole::GOALKEEPER,
        ]);

        $firstNomination = AuctionNomination::factory()->create([
            'auction_id' => $firstAuction->id,
            'auction_role_phase_id' => $firstPhase->id,
            'auction_participant_id' => $firstParticipant->id,
            'player_season_id' => $firstPlayerSeason->id,
            'status' => AuctionNominationStatus::ACTIVE,
            'turn_number' => 1,
            'expires_at' => now()->subMinutes(2),
        ]);

        $secondNomination = AuctionNomination::factory()->create([
            'auction_id' => $secondAuction->id,
            'auction_role_phase_id' => $secondPhase->id,
            'auction_participant_id' => $secondParticipant->id,
            'player_season_id' => $secondPlayerSeason->id,
            'status' => AuctionNominationStatus::ACTIVE,
            'turn_number' => 1,
            'expires_at' => now()->subMinute(),
        ]);

        app(ProcessExpiredAuctionNominationsJob::class)
            ->handle(
                app(ExpireAuctionNomination::class)
            );

        $this->assertSame(
            AuctionNominationStatus::COMPLETED,
            $firstNomination->fresh()->status
        );

        $this->assertSame(
            AuctionNominationStatus::COMPLETED,
            $secondNomination->fresh()->status
        );
    }

    public function test_processing_same_expired_nomination_twice_does_not_duplicate_acquisition(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        $phase = AuctionRolePhase::factory()
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

        $job = app(ProcessExpiredAuctionNominationsJob::class);

        $job->handle(
            app(ExpireAuctionNomination::class)
        );

        $job->handle(
            app(ExpireAuctionNomination::class)
        );

        $this->assertSame(
            90,
            $creditAccount->fresh()->current_balance
        );

        $this->assertDatabaseCount('roster_ownerships', 1);
    }
}
