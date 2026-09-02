<?php

namespace Tests\Feature\Football;

use App\Domain\Football\Enums\PlayerRole;
use App\Domain\Football\Enums\PlayerSeasonStatus;
use App\Models\Football\FootballPlayer;
use App\Models\Football\FootballSeason;
use App\Models\Football\PlayerSeason;
use App\Models\Football\RealClub;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlayerSeasonTest extends TestCase
{
    use RefreshDatabase;

    public function test_player_season_can_be_created_from_factory(): void
    {
        $playerSeason = PlayerSeason::factory()->create();

        $this->assertDatabaseHas('player_seasons', [
            'id' => $playerSeason->id,
        ]);
    }

    public function test_ulid_is_generated_automatically(): void
    {
        $playerSeason = PlayerSeason::factory()->create();

        $this->assertNotNull($playerSeason->ulid);
        $this->assertSame(26, strlen($playerSeason->ulid));
    }

    public function test_role_is_cast_to_enum(): void
    {
        $playerSeason = PlayerSeason::factory()->create([
            'role' => PlayerRole::FORWARD,
        ]);

        $this->assertSame(PlayerRole::FORWARD, $playerSeason->role);
    }

    public function test_status_is_cast_to_enum(): void
    {
        $playerSeason = PlayerSeason::factory()->create();

        $this->assertSame(
            PlayerSeasonStatus::ACTIVE,
            $playerSeason->status
        );
    }

    public function test_inactive_factory_state_sets_inactive_status(): void
    {
        $playerSeason = PlayerSeason::factory()
            ->inactive()
            ->create();

        $this->assertSame(
            PlayerSeasonStatus::INACTIVE,
            $playerSeason->status
        );
    }

    public function test_player_season_belongs_to_football_season(): void
    {
        $playerSeason = PlayerSeason::factory()->create();

        $this->assertInstanceOf(
            FootballSeason::class,
            $playerSeason->footballSeason
        );
    }

    public function test_player_season_belongs_to_football_player(): void
    {
        $playerSeason = PlayerSeason::factory()->create();

        $this->assertInstanceOf(
            FootballPlayer::class,
            $playerSeason->footballPlayer
        );
    }

    public function test_player_season_belongs_to_real_club(): void
    {
        $playerSeason = PlayerSeason::factory()->create();

        $this->assertInstanceOf(
            RealClub::class,
            $playerSeason->realClub
        );
    }

    public function test_same_player_cannot_exist_twice_in_same_football_season(): void
    {
        $footballSeason = FootballSeason::factory()->create();
        $footballPlayer = FootballPlayer::factory()->create();

        PlayerSeason::factory()->create([
            'football_season_id' => $footballSeason->id,
            'football_player_id' => $footballPlayer->id,
        ]);

        $this->expectException(QueryException::class);

        PlayerSeason::factory()->create([
            'football_season_id' => $footballSeason->id,
            'football_player_id' => $footballPlayer->id,
        ]);
    }

    public function test_same_player_can_exist_in_different_football_seasons(): void
    {
        $footballPlayer = FootballPlayer::factory()->create();

        $firstSeason = FootballSeason::factory()->create([
            'name' => 'Serie A 2026/2027',
            'start_year' => 2026,
            'end_year' => 2027,
        ]);

        $secondSeason = FootballSeason::factory()->create([
            'name' => 'Serie A 2027/2028',
            'start_year' => 2027,
            'end_year' => 2028,
        ]);

        PlayerSeason::factory()->create([
            'football_season_id' => $firstSeason->id,
            'football_player_id' => $footballPlayer->id,
        ]);

        PlayerSeason::factory()->create([
            'football_season_id' => $secondSeason->id,
            'football_player_id' => $footballPlayer->id,
        ]);

        $this->assertSame(
            2,
            PlayerSeason::where(
                'football_player_id',
                $footballPlayer->id
            )->count()
        );
    }

    public function test_inverse_relationships_return_player_seasons(): void
    {
        $playerSeason = PlayerSeason::factory()->create();

        $this->assertTrue(
            $playerSeason->footballSeason
                ->playerSeasons
                ->contains($playerSeason)
        );

        $this->assertTrue(
            $playerSeason->footballPlayer
                ->playerSeasons
                ->contains($playerSeason)
        );

        $this->assertTrue(
            $playerSeason->realClub
                ->playerSeasons
                ->contains($playerSeason)
        );
    }
}
