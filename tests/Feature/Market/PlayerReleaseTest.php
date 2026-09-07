<?php

namespace Tests\Feature\Market;

use App\Models\Market\MarketSession;
use App\Models\Market\PlayerRelease;
use App\Models\Roster\RosterOwnership;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PlayerReleaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_player_release_can_be_created_from_factory(): void
    {
        $release = PlayerRelease::factory()->create();

        $this->assertDatabaseHas('player_releases', [
            'id' => $release->id,
            'roster_ownership_id' => $release->roster_ownership_id,
        ]);
    }

    public function test_ulid_is_generated_automatically(): void
    {
        $release = PlayerRelease::factory()->create();

        $this->assertNotNull($release->ulid);
        $this->assertSame(26, strlen($release->ulid));
    }

    public function test_numeric_fields_are_cast_to_integer(): void
    {
        $release = PlayerRelease::factory()->create([
            'release_value' => 75,
        ]);

        $this->assertSame(75, $release->release_value);
    }

    public function test_released_at_is_cast_to_datetime(): void
    {
        $release = PlayerRelease::factory()->create();

        $this->assertInstanceOf(
            Carbon::class,
            $release->released_at
        );
    }

    public function test_player_release_belongs_to_market_session(): void
    {
        $release = PlayerRelease::factory()->create();

        $this->assertTrue(
            $release->marketSession->is(
                MarketSession::find($release->market_session_id)
            )
        );
    }

    public function test_player_release_belongs_to_roster_ownership(): void
    {
        $release = PlayerRelease::factory()->create();

        $this->assertTrue(
            $release->rosterOwnership->is(
                RosterOwnership::find($release->roster_ownership_id)
            )
        );
    }

    public function test_player_release_belongs_to_team(): void
    {
        $release = PlayerRelease::factory()->create();

        $this->assertTrue(
            $release->team->is($release->rosterOwnership->team)
        );
    }

    public function test_player_release_belongs_to_player_season(): void
    {
        $release = PlayerRelease::factory()->create();

        $this->assertTrue(
            $release->playerSeason->is(
                $release->rosterOwnership->playerSeason
            )
        );
    }

    public function test_inverse_relationships_return_player_release(): void
    {
        $release = PlayerRelease::factory()->create();

        $this->assertTrue(
            $release->marketSession->playerReleases->contains($release)
        );

        $this->assertTrue(
            $release->team->playerReleases->contains($release)
        );

        $this->assertTrue(
            $release->playerSeason->playerReleases->contains($release)
        );

        $this->assertTrue(
            $release->rosterOwnership->playerRelease->is($release)
        );
    }

    public function test_same_roster_ownership_cannot_be_released_twice(): void
    {
        $ownership = RosterOwnership::factory()->create();

        PlayerRelease::factory()->create([
            'roster_ownership_id' => $ownership->id,
            'team_id' => $ownership->team_id,
            'player_season_id' => $ownership->player_season_id,
        ]);

        $this->expectException(QueryException::class);

        PlayerRelease::factory()->create([
            'roster_ownership_id' => $ownership->id,
            'team_id' => $ownership->team_id,
            'player_season_id' => $ownership->player_season_id,
        ]);
    }

    public function test_release_snapshot_matches_roster_ownership(): void
    {
        $ownership = RosterOwnership::factory()->create();

        $release = PlayerRelease::factory()->create([
            'roster_ownership_id' => $ownership->id,
            'team_id' => $ownership->team_id,
            'player_season_id' => $ownership->player_season_id,
        ]);

        $this->assertSame(
            $ownership->team_id,
            $release->team_id
        );

        $this->assertSame(
            $ownership->player_season_id,
            $release->player_season_id
        );
    }
}
