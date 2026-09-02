<?php

namespace Tests\Feature\Football;

use App\Domain\Football\Enums\FootballPlayerStatus;
use App\Models\Football\FootballPlayer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FootballPlayerTest extends TestCase
{
    use RefreshDatabase;

    public function test_football_player_can_be_created_from_factory(): void
    {
        $footballPlayer = FootballPlayer::factory()->create();

        $this->assertDatabaseHas('football_players', [
            'id' => $footballPlayer->id,
        ]);
    }

    public function test_ulid_is_generated_automatically(): void
    {
        $footballPlayer = FootballPlayer::factory()->create();

        $this->assertNotNull($footballPlayer->ulid);
        $this->assertSame(26, strlen($footballPlayer->ulid));
    }

    public function test_status_is_cast_to_enum(): void
    {
        $footballPlayer = FootballPlayer::factory()->create();

        $this->assertInstanceOf(
            FootballPlayerStatus::class,
            $footballPlayer->status
        );

        $this->assertSame(
            FootballPlayerStatus::ACTIVE,
            $footballPlayer->status
        );
    }

    public function test_date_of_birth_is_cast_to_date(): void
    {
        $footballPlayer = FootballPlayer::factory()->create([
            'date_of_birth' => '1997-08-22',
        ]);

        $this->assertSame(
            '1997-08-22',
            $footballPlayer->date_of_birth->toDateString()
        );
    }

    public function test_inactive_factory_state_sets_inactive_status(): void
    {
        $footballPlayer = FootballPlayer::factory()
            ->inactive()
            ->create();

        $this->assertSame(
            FootballPlayerStatus::INACTIVE,
            $footballPlayer->status
        );
    }

    public function test_optional_fields_can_be_null(): void
    {
        $footballPlayer = FootballPlayer::factory()->create([
            'date_of_birth' => null,
            'nationality' => null,
            'photo_path' => null,
        ]);

        $this->assertNull($footballPlayer->date_of_birth);
        $this->assertNull($footballPlayer->nationality);
        $this->assertNull($footballPlayer->photo_path);
    }

    public function test_display_name_does_not_have_to_be_unique(): void
    {
        FootballPlayer::factory()->create([
            'display_name' => 'Mario Rossi',
        ]);

        $footballPlayer = FootballPlayer::factory()->create([
            'display_name' => 'Mario Rossi',
        ]);

        $this->assertDatabaseHas('football_players', [
            'id' => $footballPlayer->id,
            'display_name' => 'Mario Rossi',
        ]);
    }
}
