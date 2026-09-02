<?php

namespace Tests\Feature\Football;

use App\Domain\Football\Enums\FootballSeasonStatus;
use App\Models\Football\FootballSeason;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class FootballSeasonTest extends TestCase
{
    use RefreshDatabase;

    public function test_football_season_can_be_created_from_factory(): void
    {
        $footballSeason = FootballSeason::factory()->create();

        $this->assertDatabaseHas('football_seasons', [
            'id' => $footballSeason->id,
        ]);
    }

    public function test_ulid_is_generated_automatically(): void
    {
        $footballSeason = FootballSeason::factory()->create();

        $this->assertNotNull($footballSeason->ulid);
        $this->assertSame(26, strlen($footballSeason->ulid));
    }

    public function test_status_is_cast_to_enum(): void
    {
        $footballSeason = FootballSeason::factory()->create();

        $this->assertInstanceOf(
            FootballSeasonStatus::class,
            $footballSeason->status
        );

        $this->assertSame(
            FootballSeasonStatus::DRAFT,
            $footballSeason->status
        );
    }

    public function test_numeric_fields_are_cast_to_integer(): void
    {
        $footballSeason = FootballSeason::factory()->create();

        $this->assertIsInt($footballSeason->start_year);
        $this->assertIsInt($footballSeason->end_year);
    }

    public function test_dates_are_cast_to_datetime(): void
    {
        $footballSeason = FootballSeason::factory()->create([
            'starts_at' => now(),
            'ends_at' => now()->addMonths(9),
        ]);

        $this->assertInstanceOf(
            Carbon::class,
            $footballSeason->starts_at
        );

        $this->assertInstanceOf(
            Carbon::class,
            $footballSeason->ends_at
        );
    }

    public function test_active_factory_state_sets_active_status(): void
    {
        $footballSeason = FootballSeason::factory()
            ->active()
            ->create();

        $this->assertSame(
            FootballSeasonStatus::ACTIVE,
            $footballSeason->status
        );
    }

    public function test_completed_factory_state_sets_completed_status(): void
    {
        $footballSeason = FootballSeason::factory()
            ->completed()
            ->create();

        $this->assertSame(
            FootballSeasonStatus::COMPLETED,
            $footballSeason->status
        );
    }

    public function test_same_years_cannot_be_duplicated(): void
    {
        FootballSeason::factory()->create([
            'start_year' => 2026,
            'end_year' => 2027,
        ]);

        $this->expectException(QueryException::class);

        FootballSeason::factory()->create([
            'start_year' => 2026,
            'end_year' => 2027,
        ]);
    }
}
