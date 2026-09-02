<?php

namespace Tests\Feature\Season;

use App\Domain\Season\Enums\SeasonParticipationStatus;
use App\Models\League\League;
use App\Models\League\LeagueMembership;
use App\Models\Season\LeagueSeason;
use App\Models\Season\SeasonParticipation;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SeasonParticipationTest extends TestCase
{
    use RefreshDatabase;

    public function test_season_participation_can_be_created_from_factory(): void
    {
        $participation = SeasonParticipation::factory()->create();

        $this->assertDatabaseHas('season_participations', [
            'id' => $participation->id,
        ]);
    }

    public function test_ulid_is_generated_automatically(): void
    {
        $participation = SeasonParticipation::factory()->create();

        $this->assertNotNull($participation->ulid);
        $this->assertSame(26, strlen($participation->ulid));
    }

    public function test_status_is_cast_to_enum(): void
    {
        $participation = SeasonParticipation::factory()->create();

        $this->assertInstanceOf(
            SeasonParticipationStatus::class,
            $participation->status
        );

        $this->assertSame(
            SeasonParticipationStatus::ACTIVE,
            $participation->status
        );
    }

    public function test_joined_at_is_cast_to_datetime(): void
    {
        $participation = SeasonParticipation::factory()->create();

        $this->assertInstanceOf(
            Carbon::class,
            $participation->joined_at
        );
    }

    public function test_inactive_factory_state_sets_inactive_status(): void
    {
        $participation = SeasonParticipation::factory()
            ->inactive()
            ->create();

        $this->assertSame(
            SeasonParticipationStatus::INACTIVE,
            $participation->status
        );
    }

    public function test_same_membership_cannot_participate_twice_in_same_season(): void
    {
        $league = League::factory()->create();

        $season = LeagueSeason::factory()->create([
            'league_id' => $league->id,
        ]);

        $membership = LeagueMembership::factory()->create([
            'league_id' => $league->id,
        ]);

        SeasonParticipation::factory()->create([
            'league_season_id' => $season->id,
            'league_membership_id' => $membership->id,
        ]);

        $this->expectException(QueryException::class);

        SeasonParticipation::factory()->create([
            'league_season_id' => $season->id,
            'league_membership_id' => $membership->id,
        ]);
    }

    public function test_season_participation_belongs_to_league_season(): void
    {
        $season = LeagueSeason::factory()->create();

        $participation = SeasonParticipation::factory()->create([
            'league_season_id' => $season->id,
        ]);

        $this->assertTrue(
            $participation->leagueSeason->is($season)
        );
    }

    public function test_season_participation_belongs_to_league_membership(): void
    {
        $membership = LeagueMembership::factory()->create();

        $participation = SeasonParticipation::factory()->create([
            'league_membership_id' => $membership->id,
        ]);

        $this->assertTrue(
            $participation->leagueMembership->is($membership)
        );
    }

    public function test_league_season_has_participations(): void
    {
        $season = LeagueSeason::factory()->create();

        $participation = SeasonParticipation::factory()->create([
            'league_season_id' => $season->id,
        ]);

        $this->assertTrue(
            $season->participations->contains($participation)
        );
    }

    public function test_league_membership_has_season_participations(): void
    {
        $membership = LeagueMembership::factory()->create();

        $participation = SeasonParticipation::factory()->create([
            'league_membership_id' => $membership->id,
        ]);

        $this->assertTrue(
            $membership->seasonParticipations->contains($participation)
        );
    }
}
