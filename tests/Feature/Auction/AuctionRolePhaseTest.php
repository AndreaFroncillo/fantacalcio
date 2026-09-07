<?php

namespace Tests\Feature\Auction;

use App\Domain\Auction\Enums\AuctionRolePhaseStatus;
use App\Domain\Football\Enums\PlayerRole;
use App\Models\Auction\Auction;
use App\Models\Auction\AuctionRolePhase;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AuctionRolePhaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_auction_role_phase_can_be_created_from_factory(): void
    {
        $phase = AuctionRolePhase::factory()->create();

        $this->assertDatabaseHas('auction_role_phases', [
            'id' => $phase->id,
            'auction_id' => $phase->auction_id,
        ]);
    }

    public function test_ulid_is_generated_automatically(): void
    {
        $phase = AuctionRolePhase::factory()->create();

        $this->assertNotNull($phase->ulid);
        $this->assertSame(26, strlen($phase->ulid));
    }

    public function test_role_is_cast_to_player_role_enum(): void
    {
        $phase = AuctionRolePhase::factory()->create();

        $this->assertSame(PlayerRole::GOALKEEPER, $phase->role);
    }

    public function test_status_is_cast_to_enum(): void
    {
        $phase = AuctionRolePhase::factory()->create();

        $this->assertSame(
            AuctionRolePhaseStatus::PENDING,
            $phase->status
        );
    }

    public function test_position_is_cast_to_integer(): void
    {
        $phase = AuctionRolePhase::factory()->create([
            'position' => 3,
        ]);

        $this->assertSame(3, $phase->position);
    }

    public function test_dates_are_cast_to_datetime(): void
    {
        $phase = AuctionRolePhase::factory()->completed()->create();

        $this->assertInstanceOf(Carbon::class, $phase->started_at);
        $this->assertInstanceOf(Carbon::class, $phase->completed_at);
    }

    public function test_dates_can_be_null(): void
    {
        $phase = AuctionRolePhase::factory()->create();

        $this->assertNull($phase->started_at);
        $this->assertNull($phase->completed_at);
    }

    public function test_auction_role_phase_belongs_to_auction(): void
    {
        $phase = AuctionRolePhase::factory()->create();

        $this->assertTrue(
            $phase->auction->is(
                Auction::find($phase->auction_id)
            )
        );
    }

    public function test_auction_has_role_phases_ordered_by_position(): void
    {
        $auction = Auction::factory()->create();

        AuctionRolePhase::factory()->create([
            'auction_id' => $auction->id,
            'role' => PlayerRole::FORWARD,
            'position' => 4,
        ]);

        AuctionRolePhase::factory()->create([
            'auction_id' => $auction->id,
            'role' => PlayerRole::GOALKEEPER,
            'position' => 1,
        ]);

        AuctionRolePhase::factory()->create([
            'auction_id' => $auction->id,
            'role' => PlayerRole::MIDFIELDER,
            'position' => 3,
        ]);

        AuctionRolePhase::factory()->create([
            'auction_id' => $auction->id,
            'role' => PlayerRole::DEFENDER,
            'position' => 2,
        ]);

        $this->assertSame(
            [
                PlayerRole::GOALKEEPER,
                PlayerRole::DEFENDER,
                PlayerRole::MIDFIELDER,
                PlayerRole::FORWARD,
            ],
            $auction->rolePhases->pluck('role')->all()
        );
    }

    public function test_same_role_cannot_exist_twice_in_same_auction(): void
    {
        $auction = Auction::factory()->create();

        AuctionRolePhase::factory()->create([
            'auction_id' => $auction->id,
            'role' => PlayerRole::GOALKEEPER,
            'position' => 1,
        ]);

        $this->expectException(QueryException::class);

        AuctionRolePhase::factory()->create([
            'auction_id' => $auction->id,
            'role' => PlayerRole::GOALKEEPER,
            'position' => 2,
        ]);
    }

    public function test_same_position_cannot_exist_twice_in_same_auction(): void
    {
        $auction = Auction::factory()->create();

        AuctionRolePhase::factory()->create([
            'auction_id' => $auction->id,
            'role' => PlayerRole::GOALKEEPER,
            'position' => 1,
        ]);

        $this->expectException(QueryException::class);

        AuctionRolePhase::factory()->create([
            'auction_id' => $auction->id,
            'role' => PlayerRole::DEFENDER,
            'position' => 1,
        ]);
    }

    public function test_same_role_can_exist_in_different_auctions(): void
    {
        AuctionRolePhase::factory()->create([
            'role' => PlayerRole::GOALKEEPER,
            'position' => 1,
        ]);

        $phase = AuctionRolePhase::factory()->create([
            'role' => PlayerRole::DEFENDER,
            'position' => 1,
        ]);

        $this->assertDatabaseHas('auction_role_phases', [
            'id' => $phase->id,
        ]);
    }

    public function test_active_factory_state_sets_active_status_and_started_at(): void
    {
        $phase = AuctionRolePhase::factory()->active()->create();

        $this->assertSame(AuctionRolePhaseStatus::ACTIVE, $phase->status);
        $this->assertNotNull($phase->started_at);
        $this->assertNull($phase->completed_at);
    }

    public function test_completed_factory_state_sets_completed_status_and_dates(): void
    {
        $phase = AuctionRolePhase::factory()->completed()->create();

        $this->assertSame(AuctionRolePhaseStatus::COMPLETED, $phase->status);
        $this->assertNotNull($phase->started_at);
        $this->assertNotNull($phase->completed_at);
    }
}
