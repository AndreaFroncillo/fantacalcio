<?php

namespace Tests\Feature\League;

use App\Models\League\League;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeagueTest extends TestCase
{
    use RefreshDatabase;

    public function test_league_can_be_created_from_factory(): void
    {
        $league = League::factory()->create();

        $this->assertDatabaseHas('leagues', [
            'id' => $league->id,
            'name' => $league->name,
            'timezone' => $league->timezone,
        ]);
    }

    public function test_ulid_is_generated_automatically(): void
    {
        $league = League::factory()->create();

        $this->assertNotNull($league->ulid);
        $this->assertSame(26, strlen($league->ulid));
    }

    public function test_leagues_have_unique_ulids(): void
    {
        $firstLeague = League::factory()->create();
        $secondLeague = League::factory()->create();

        $this->assertNotSame(
            $firstLeague->ulid,
            $secondLeague->ulid
        );
    }

    public function test_join_code_can_be_null(): void
    {
        $league = League::factory()->create([
            'join_code' => null,
        ]);

        $this->assertNull($league->join_code);

        $this->assertDatabaseHas('leagues', [
            'id' => $league->id,
            'join_code' => null,
        ]);
    }

    public function test_join_code_must_be_unique_when_present(): void
    {
        League::factory()->create([
            'join_code' => 'PALAZZI26',
        ]);

        $this->expectException(QueryException::class);

        League::factory()->create([
            'join_code' => 'PALAZZI26',
        ]);
    }

    public function test_timezone_is_required(): void
    {
        $this->expectException(QueryException::class);

        League::query()->insert([
            'ulid' => '01M1AAAAAAAAAAAAAAAAAAAAAA',
            'name' => 'Lega Test',
            'description' => null,
            'join_code' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
