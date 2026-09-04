<?php

namespace Tests\Feature\Roster;

use App\Models\Football\PlayerSeason;
use App\Models\Roster\RosterOwnership;
use App\Models\Season\LeagueSeason;
use App\Models\Team\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class RosterOwnershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_roster_ownership_can_be_created_from_factory(): void
    {
        $ownership = RosterOwnership::factory()->create();

        $this->assertDatabaseHas('roster_ownerships', [
            'id' => $ownership->id,
            'team_id' => $ownership->team_id,
            'player_season_id' => $ownership->player_season_id,
        ]);
    }

    public function test_ulid_is_generated_automatically(): void
    {
        $ownership = RosterOwnership::factory()->create();

        $this->assertNotNull($ownership->ulid);
        $this->assertSame(26, strlen($ownership->ulid));
    }

    public function test_acquisition_value_is_cast_to_integer(): void
    {
        $ownership = RosterOwnership::factory()->create([
            'acquisition_value' => 75,
        ]);

        $this->assertSame(75, $ownership->acquisition_value);
    }

    public function test_dates_are_cast_to_datetime(): void
    {
        $ownership = RosterOwnership::factory()->create();

        $this->assertInstanceOf(
            Carbon::class,
            $ownership->acquired_at
        );

        $this->assertNull($ownership->released_at);
    }

    public function test_released_factory_state_sets_released_at(): void
    {
        $ownership = RosterOwnership::factory()
            ->released()
            ->create();

        $this->assertNotNull($ownership->released_at);

        $this->assertInstanceOf(
            Carbon::class,
            $ownership->released_at
        );
    }

    public function test_roster_ownership_belongs_to_league_season(): void
    {
        $leagueSeason = LeagueSeason::factory()->create();

        $ownership = RosterOwnership::factory()->create([
            'league_season_id' => $leagueSeason->id,
        ]);

        $this->assertTrue(
            $ownership->leagueSeason->is($leagueSeason)
        );
    }

    public function test_roster_ownership_belongs_to_team(): void
    {
        $team = Team::factory()->create();

        $ownership = RosterOwnership::factory()->create([
            'team_id' => $team->id,
        ]);

        $this->assertTrue(
            $ownership->team->is($team)
        );
    }

    public function test_roster_ownership_belongs_to_player_season(): void
    {
        $playerSeason = PlayerSeason::factory()->create();

        $ownership = RosterOwnership::factory()->create([
            'player_season_id' => $playerSeason->id,
        ]);

        $this->assertTrue(
            $ownership->playerSeason->is($playerSeason)
        );
    }

    public function test_inverse_relationships_return_roster_ownerships(): void
    {
        $ownership = RosterOwnership::factory()->create();

        $this->assertTrue(
            $ownership->leagueSeason->rosterOwnerships->contains($ownership)
        );

        $this->assertTrue(
            $ownership->team->rosterOwnerships->contains($ownership)
        );

        $this->assertTrue(
            $ownership->playerSeason->rosterOwnerships->contains($ownership)
        );
    }

    public function test_same_player_can_have_historical_ownerships_in_same_league_season(): void
    {
        $leagueSeason = LeagueSeason::factory()->create();
        $playerSeason = PlayerSeason::factory()->create();

        RosterOwnership::factory()
            ->released()
            ->create([
                'league_season_id' => $leagueSeason->id,
                'player_season_id' => $playerSeason->id,
            ]);

        $activeOwnership = RosterOwnership::factory()->create([
            'league_season_id' => $leagueSeason->id,
            'player_season_id' => $playerSeason->id,
        ]);

        $this->assertDatabaseCount('roster_ownerships', 2);

        $this->assertNull($activeOwnership->released_at);
    }
}
