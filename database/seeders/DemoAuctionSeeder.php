<?php

namespace Database\Seeders;

use App\Domain\Football\Enums\PlayerRole;
use App\Models\Auction\Auction;
use App\Models\Auction\AuctionParticipant;
use App\Models\Auction\AuctionRolePhase;
use App\Models\Credit\TeamCreditAccount;
use App\Models\Football\FootballSeason;
use App\Models\Football\PlayerSeason;
use App\Models\League\League;
use App\Models\League\LeagueMembership;
use App\Models\Market\MarketSession;
use App\Models\Roster\LeagueSeasonRosterRule;
use App\Models\Season\LeagueSeason;
use App\Models\Season\SeasonParticipation;
use App\Models\Team\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoAuctionSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Andrea Demo',
            'email' => 'demo@fantacalcio.test',
            'password' => Hash::make('password'),
        ]);

        $league = League::factory()->create([
            'name' => 'Lega Demo',
        ]);

        $membership = LeagueMembership::factory()
            ->president()
            ->create([
                'league_id' => $league->id,
                'user_id' => $user->id,
            ]);

        $leagueSeason = LeagueSeason::factory()
            ->active()
            ->create([
                'league_id' => $league->id,
                'start_year' => 2026,
                'end_year' => 2027,
                'initial_credits' => 500,
            ]);

        LeagueSeasonRosterRule::factory()->create([
            'league_season_id' => $leagueSeason->id,
            'role' => PlayerRole::GOALKEEPER,
            'max_players' => 3,
        ]);

        $participation = SeasonParticipation::factory()->create([
            'league_season_id' => $leagueSeason->id,
            'league_membership_id' => $membership->id,
        ]);

        $team = Team::factory()->create([
            'season_participation_id' => $participation->id,
            'name' => 'Andrea FC',
            'short_name' => 'AFC',
        ]);

        TeamCreditAccount::factory()->create([
            'team_id' => $team->id,
            'initial_balance' => 500,
            'current_balance' => 500,
        ]);

        $marketSession = MarketSession::factory()
            ->open()
            ->create([
                'league_season_id' => $leagueSeason->id,
                'name' => 'Asta Estiva Demo',
            ]);

        $auction = Auction::factory()
            ->live()
            ->create([
                'market_session_id' => $marketSession->id,
            ]);

        AuctionRolePhase::factory()
            ->active()
            ->create([
                'auction_id' => $auction->id,
                'role' => PlayerRole::GOALKEEPER,
                'position' => 1,
            ]);

        AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'team_id' => $team->id,
            'nomination_position' => 1,
        ]);

        $footballSeason = FootballSeason::factory()
            ->active()
            ->create([
                'name' => 'Serie A 2026/2027',
                'start_year' => 2026,
                'end_year' => 2027,
            ]);

        PlayerSeason::factory()
            ->count(20)
            ->create([
                'football_season_id' => $footballSeason->id,
                'role' => PlayerRole::GOALKEEPER,
            ]);

        $this->command?->info(
            'Demo user: demo@fantacalcio.test'
        );

        $this->command?->info(
            'Password: password'
        );

        $this->command?->info(
            "Auction ULID: {$auction->ulid}"
        );

        $this->command?->info(
            "Auction URL: http://127.0.0.1:8000/auctions/{$auction->ulid}"
        );
    }
}
