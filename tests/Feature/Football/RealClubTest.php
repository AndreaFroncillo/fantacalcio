<?php

namespace Tests\Feature\Football;

use App\Domain\Football\Enums\RealClubStatus;
use App\Models\Football\RealClub;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RealClubTest extends TestCase
{
    use RefreshDatabase;

    public function test_real_club_can_be_created_from_factory(): void
    {
        $realClub = RealClub::factory()->create();

        $this->assertDatabaseHas('real_clubs', [
            'id' => $realClub->id,
        ]);
    }

    public function test_ulid_is_generated_automatically(): void
    {
        $realClub = RealClub::factory()->create();

        $this->assertNotNull($realClub->ulid);
        $this->assertSame(26, strlen($realClub->ulid));
    }

    public function test_status_is_cast_to_enum(): void
    {
        $realClub = RealClub::factory()->create();

        $this->assertInstanceOf(
            RealClubStatus::class,
            $realClub->status
        );

        $this->assertSame(
            RealClubStatus::ACTIVE,
            $realClub->status
        );
    }

    public function test_inactive_factory_state_sets_inactive_status(): void
    {
        $realClub = RealClub::factory()
            ->inactive()
            ->create();

        $this->assertSame(
            RealClubStatus::INACTIVE,
            $realClub->status
        );
    }

    public function test_slug_must_be_unique(): void
    {
        RealClub::factory()->create([
            'slug' => 'inter',
        ]);

        $this->expectException(QueryException::class);

        RealClub::factory()->create([
            'slug' => 'inter',
        ]);
    }

    public function test_optional_fields_can_be_null(): void
    {
        $realClub = RealClub::factory()->create([
            'short_name' => null,
            'logo_path' => null,
        ]);

        $this->assertNull($realClub->short_name);
        $this->assertNull($realClub->logo_path);
    }
}
