<?php

namespace Tests\Feature\Roster;

use App\Domain\Football\Enums\PlayerRole;
use App\Models\Roster\LeagueSeasonRosterRule;
use App\Models\Season\LeagueSeason;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeagueSeasonRosterRuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_roster_rule_can_be_created_from_factory(): void
    {
        $rosterRule = LeagueSeasonRosterRule::factory()->create();

        $this->assertDatabaseHas('league_season_roster_rules', [
            'id' => $rosterRule->id,
        ]);
    }

    public function test_ulid_is_generated_automatically(): void
    {
        $rosterRule = LeagueSeasonRosterRule::factory()->create();

        $this->assertNotNull($rosterRule->ulid);
        $this->assertSame(26, strlen($rosterRule->ulid));
    }

    public function test_role_is_cast_to_player_role_enum(): void
    {
        $rosterRule = LeagueSeasonRosterRule::factory()->create([
            'role' => PlayerRole::GOALKEEPER,
        ]);

        $this->assertSame(
            PlayerRole::GOALKEEPER,
            $rosterRule->role
        );
    }

    public function test_max_players_is_cast_to_integer(): void
    {
        $rosterRule = LeagueSeasonRosterRule::factory()->create([
            'max_players' => 3,
        ]);

        $this->assertIsInt($rosterRule->max_players);
        $this->assertSame(3, $rosterRule->max_players);
    }

    public function test_roster_rule_belongs_to_league_season(): void
    {
        $rosterRule = LeagueSeasonRosterRule::factory()->create();

        $this->assertTrue(
            $rosterRule->leagueSeason->is(
                LeagueSeason::findOrFail($rosterRule->league_season_id)
            )
        );
    }

    public function test_league_season_has_roster_rules(): void
    {
        $leagueSeason = LeagueSeason::factory()->create();

        $rosterRule = LeagueSeasonRosterRule::factory()->create([
            'league_season_id' => $leagueSeason->id,
        ]);

        $this->assertTrue(
            $leagueSeason->rosterRules->contains($rosterRule)
        );
    }

    public function test_same_role_cannot_be_defined_twice_in_same_league_season(): void
    {
        $leagueSeason = LeagueSeason::factory()->create();

        LeagueSeasonRosterRule::factory()->create([
            'league_season_id' => $leagueSeason->id,
            'role' => PlayerRole::GOALKEEPER,
        ]);

        $this->expectException(QueryException::class);

        LeagueSeasonRosterRule::factory()->create([
            'league_season_id' => $leagueSeason->id,
            'role' => PlayerRole::GOALKEEPER,
        ]);
    }

    public function test_same_role_can_be_defined_in_different_league_seasons(): void
    {
        LeagueSeasonRosterRule::factory()->create([
            'role' => PlayerRole::GOALKEEPER,
        ]);

        LeagueSeasonRosterRule::factory()->create([
            'role' => PlayerRole::GOALKEEPER,
        ]);

        $this->assertSame(
            2,
            LeagueSeasonRosterRule::where(
                'role',
                PlayerRole::GOALKEEPER->value
            )->count()
        );
    }
}
