<?php

namespace Tests\Feature\Season;

use App\Domain\Season\Enums\LeagueSeasonStatus;
use App\Models\League\League;
use App\Models\Season\LeagueSeason;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class LeagueSeasonTest extends TestCase
{
    use RefreshDatabase;

    public function test_league_season_can_be_created_from_factory(): void
    {
        $season = LeagueSeason::factory()->create();

        $this->assertDatabaseHas('league_seasons', [
            'id' => $season->id,
            'league_id' => $season->league_id,
            'start_year' => $season->start_year,
            'end_year' => $season->end_year,
        ]);
    }

    public function test_ulid_is_generated_automatically(): void
    {
        $season = LeagueSeason::factory()->create();

        $this->assertNotNull($season->ulid);
        $this->assertSame(26, strlen($season->ulid));
    }

    public function test_status_is_cast_to_enum(): void
    {
        $season = LeagueSeason::factory()->create();

        $this->assertSame(
            LeagueSeasonStatus::DRAFT,
            $season->status
        );
    }

    public function test_numeric_fields_are_cast_to_integer(): void
    {
        $season = LeagueSeason::factory()->create();

        $this->assertIsInt($season->start_year);
        $this->assertIsInt($season->end_year);
        $this->assertIsInt($season->max_teams);
        $this->assertIsInt($season->initial_credits);
    }

    public function test_dates_are_cast_to_datetime(): void
    {
        $season = LeagueSeason::factory()->create([
            'starts_at' => now(),
            'ends_at' => now()->addMonths(9),
        ]);

        $this->assertInstanceOf(Carbon::class, $season->starts_at);
        $this->assertInstanceOf(Carbon::class, $season->ends_at);
    }

    public function test_active_factory_state_sets_active_status(): void
    {
        $season = LeagueSeason::factory()
            ->active()
            ->create();

        $this->assertSame(
            LeagueSeasonStatus::ACTIVE,
            $season->status
        );
    }

    public function test_completed_factory_state_sets_completed_status(): void
    {
        $season = LeagueSeason::factory()
            ->completed()
            ->create();

        $this->assertSame(
            LeagueSeasonStatus::COMPLETED,
            $season->status
        );
    }

    public function test_same_season_cannot_be_duplicated_in_same_league(): void
    {
        $league = League::factory()->create();

        LeagueSeason::factory()->create([
            'league_id' => $league->id,
            'start_year' => 2026,
            'end_year' => 2027,
        ]);

        $this->expectException(QueryException::class);

        LeagueSeason::factory()->create([
            'league_id' => $league->id,
            'start_year' => 2026,
            'end_year' => 2027,
        ]);
    }

    public function test_same_years_can_exist_in_different_leagues(): void
    {
        LeagueSeason::factory()->create([
            'start_year' => 2026,
            'end_year' => 2027,
        ]);

        $season = LeagueSeason::factory()->create([
            'start_year' => 2026,
            'end_year' => 2027,
        ]);

        $this->assertDatabaseHas('league_seasons', [
            'id' => $season->id,
        ]);
    }

    public function test_league_season_belongs_to_league(): void
    {
        $season = LeagueSeason::factory()->create();

        $this->assertInstanceOf(
            League::class,
            $season->league
        );
    }

    public function test_league_has_seasons(): void
    {
        $league = League::factory()->create();

        $season = LeagueSeason::factory()->create([
            'league_id' => $league->id,
        ]);

        $this->assertTrue(
            $league->seasons->contains($season)
        );
    }
}
