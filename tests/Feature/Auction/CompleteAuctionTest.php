<?php

namespace Tests\Feature\Auction;

use App\Domain\Auction\Actions\CompleteAuction;
use App\Domain\Auction\Enums\AuctionNominationStatus;
use App\Domain\Auction\Enums\AuctionRolePhaseStatus;
use App\Domain\Auction\Enums\AuctionStatus;
use App\Domain\Football\Enums\PlayerRole;
use App\Events\Auction\AuctionCompleted;
use App\Models\Auction\Auction;
use App\Models\Auction\AuctionNomination;
use App\Models\Auction\AuctionRolePhase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use RuntimeException;
use Tests\TestCase;

class CompleteAuctionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([
            AuctionCompleted::class,
        ]);
    }

    public function test_it_fails_when_auction_is_not_live(): void
    {
        $auction = Auction::factory()->create();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Auction is not live.'
        );

        app(CompleteAuction::class)
            ->execute($auction);
    }

    public function test_it_fails_when_auction_has_active_nomination(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'status' => AuctionNominationStatus::ACTIVE,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Auction has an active nomination.'
        );

        app(CompleteAuction::class)
            ->execute($auction);
    }

    public function test_it_fails_when_auction_has_incomplete_role_phase(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        AuctionRolePhase::factory()->create([
            'auction_id' => $auction->id,
            'role' => PlayerRole::FORWARD,
            'position' => 4,
            'status' => AuctionRolePhaseStatus::ACTIVE,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Auction has incomplete role phases.'
        );

        app(CompleteAuction::class)
            ->execute($auction);
    }

    public function test_it_completes_auction_when_all_role_phases_are_completed(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        AuctionRolePhase::factory()->create([
            'auction_id' => $auction->id,
            'role' => PlayerRole::GOALKEEPER,
            'position' => 1,
            'status' => AuctionRolePhaseStatus::COMPLETED,
            'completed_at' => now(),
        ]);

        AuctionRolePhase::factory()->create([
            'auction_id' => $auction->id,
            'role' => PlayerRole::DEFENDER,
            'position' => 2,
            'status' => AuctionRolePhaseStatus::COMPLETED,
            'completed_at' => now(),
        ]);

        AuctionRolePhase::factory()->create([
            'auction_id' => $auction->id,
            'role' => PlayerRole::MIDFIELDER,
            'position' => 3,
            'status' => AuctionRolePhaseStatus::COMPLETED,
            'completed_at' => now(),
        ]);

        AuctionRolePhase::factory()->create([
            'auction_id' => $auction->id,
            'role' => PlayerRole::FORWARD,
            'position' => 4,
            'status' => AuctionRolePhaseStatus::COMPLETED,
            'completed_at' => now(),
        ]);

        $result = app(CompleteAuction::class)
            ->execute($auction);

        $this->assertSame(
            AuctionStatus::COMPLETED,
            $result->status
        );

        $this->assertNotNull(
            $result->completed_at
        );

        $this->assertDatabaseHas('auctions', [
            'id' => $auction->id,
            'status' => AuctionStatus::COMPLETED->value,
        ]);
    }

    public function test_it_does_not_modify_market_session_when_completing_auction(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        AuctionRolePhase::factory()->create([
            'auction_id' => $auction->id,
            'role' => PlayerRole::GOALKEEPER,
            'position' => 1,
            'status' => AuctionRolePhaseStatus::COMPLETED,
            'completed_at' => now(),
        ]);

        AuctionRolePhase::factory()->create([
            'auction_id' => $auction->id,
            'role' => PlayerRole::DEFENDER,
            'position' => 2,
            'status' => AuctionRolePhaseStatus::COMPLETED,
            'completed_at' => now(),
        ]);

        AuctionRolePhase::factory()->create([
            'auction_id' => $auction->id,
            'role' => PlayerRole::MIDFIELDER,
            'position' => 3,
            'status' => AuctionRolePhaseStatus::COMPLETED,
            'completed_at' => now(),
        ]);

        AuctionRolePhase::factory()->create([
            'auction_id' => $auction->id,
            'role' => PlayerRole::FORWARD,
            'position' => 4,
            'status' => AuctionRolePhaseStatus::COMPLETED,
            'completed_at' => now(),
        ]);

        $marketSession = $auction
            ->marketSession()
            ->firstOrFail();

        $originalStatus = $marketSession->status;
        $originalStartsAt = $marketSession->starts_at;
        $originalEndsAt = $marketSession->ends_at;

        app(CompleteAuction::class)
            ->execute($auction);

        $marketSession->refresh();

        $this->assertSame(
            $originalStatus,
            $marketSession->status
        );

        $this->assertEquals(
            $originalStartsAt,
            $marketSession->starts_at
        );

        $this->assertEquals(
            $originalEndsAt,
            $marketSession->ends_at
        );
    }

    public function test_it_dispatches_realtime_event_when_auction_is_completed(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        AuctionRolePhase::factory()
            ->completed()
            ->create([
                'auction_id' => $auction->id,
                'role' => PlayerRole::GOALKEEPER,
                'position' => 1,
            ]);

        $result = app(CompleteAuction::class)
            ->execute($auction);

        Event::assertDispatched(
            AuctionCompleted::class,
            function (AuctionCompleted $event) use ($result) {
                return $event->auction->is($result)
                    && $event->auction->status === AuctionStatus::COMPLETED;
            }
        );
    }

    public function test_it_does_not_dispatch_realtime_event_when_auction_completion_fails(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        AuctionRolePhase::factory()
            ->active()
            ->create([
                'auction_id' => $auction->id,
                'role' => PlayerRole::GOALKEEPER,
                'position' => 1,
            ]);

        try {
            app(CompleteAuction::class)
                ->execute($auction);
        } catch (RuntimeException) {
            //
        }

        Event::assertNotDispatched(
            AuctionCompleted::class
        );
    }
}
