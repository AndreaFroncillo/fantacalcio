<?php

namespace Tests\Feature\Team;

use App\Domain\Team\Enums\TeamStatus;
use App\Models\Season\SeasonParticipation;
use App\Models\Team\Team;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamTest extends TestCase
{
    use RefreshDatabase;

    public function test_team_can_be_created_from_factory(): void
    {
        $team = Team::factory()->create();

        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
        ]);
    }

    public function test_ulid_is_generated_automatically(): void
    {
        $team = Team::factory()->create();

        $this->assertNotNull($team->ulid);
        $this->assertSame(26, strlen($team->ulid));
    }

    public function test_status_is_cast_to_enum(): void
    {
        $team = Team::factory()->create();

        $this->assertInstanceOf(
            TeamStatus::class,
            $team->status
        );

        $this->assertSame(
            TeamStatus::ACTIVE,
            $team->status
        );
    }

    public function test_inactive_factory_state_sets_inactive_status(): void
    {
        $team = Team::factory()
            ->inactive()
            ->create();

        $this->assertSame(
            TeamStatus::INACTIVE,
            $team->status
        );
    }

    public function test_team_belongs_to_season_participation(): void
    {
        $participation = SeasonParticipation::factory()->create();

        $team = Team::factory()->create([
            'season_participation_id' => $participation->id,
        ]);

        $this->assertTrue(
            $team->seasonParticipation->is($participation)
        );
    }

    public function test_season_participation_has_team(): void
    {
        $participation = SeasonParticipation::factory()->create();

        $team = Team::factory()->create([
            'season_participation_id' => $participation->id,
        ]);

        $this->assertTrue(
            $participation->team->is($team)
        );
    }

    public function test_same_season_participation_cannot_have_two_teams(): void
    {
        $participation = SeasonParticipation::factory()->create();

        Team::factory()->create([
            'season_participation_id' => $participation->id,
        ]);

        $this->expectException(QueryException::class);

        Team::factory()->create([
            'season_participation_id' => $participation->id,
        ]);
    }

    public function test_optional_team_fields_can_be_null(): void
    {
        $team = Team::factory()->create([
            'short_name' => null,
            'logo_path' => null,
        ]);

        $this->assertNull($team->short_name);
        $this->assertNull($team->logo_path);
    }
}
