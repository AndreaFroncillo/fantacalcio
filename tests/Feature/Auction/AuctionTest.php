<?php

namespace Tests\Feature\Auction;

use App\Domain\Auction\Enums\AuctionStatus;
use App\Models\Auction\Auction;
use App\Models\Market\MarketSession;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AuctionTest extends TestCase
{
    use RefreshDatabase;

    public function test_auction_can_be_created_from_factory(): void
    {
        $auction = Auction::factory()->create();

        $this->assertDatabaseHas('auctions', [
            'id' => $auction->id,
            'market_session_id' => $auction->market_session_id,
        ]);
    }

    public function test_ulid_is_generated_automatically(): void
    {
        $auction = Auction::factory()->create();

        $this->assertNotNull($auction->ulid);
        $this->assertSame(26, strlen($auction->ulid));
    }

    public function test_status_is_cast_to_enum(): void
    {
        $auction = Auction::factory()->create();

        $this->assertSame(
            AuctionStatus::SCHEDULED,
            $auction->status
        );
    }

    public function test_timer_fields_are_cast_to_integer(): void
    {
        $auction = Auction::factory()->create([
            'base_timer_seconds' => 90,
            'bid_extension_seconds' => 15,
        ]);

        $this->assertSame(90, $auction->base_timer_seconds);
        $this->assertSame(15, $auction->bid_extension_seconds);
    }

    public function test_dates_are_cast_to_datetime(): void
    {
        $auction = Auction::factory()->completed()->create();

        $this->assertInstanceOf(Carbon::class, $auction->started_at);
        $this->assertInstanceOf(Carbon::class, $auction->completed_at);
    }

    public function test_dates_can_be_null(): void
    {
        $auction = Auction::factory()->create();

        $this->assertNull($auction->started_at);
        $this->assertNull($auction->completed_at);
    }

    public function test_auction_belongs_to_market_session(): void
    {
        $auction = Auction::factory()->create();

        $this->assertTrue(
            $auction->marketSession->is(
                MarketSession::find($auction->market_session_id)
            )
        );
    }

    public function test_market_session_has_auction(): void
    {
        $auction = Auction::factory()->create();

        $this->assertTrue(
            $auction->marketSession->auction->is($auction)
        );
    }

    public function test_market_session_cannot_have_two_auctions(): void
    {
        $marketSession = MarketSession::factory()->create();

        Auction::factory()->create([
            'market_session_id' => $marketSession->id,
        ]);

        $this->expectException(QueryException::class);

        Auction::factory()->create([
            'market_session_id' => $marketSession->id,
        ]);
    }

    public function test_live_factory_state_sets_live_status_and_started_at(): void
    {
        $auction = Auction::factory()->live()->create();

        $this->assertSame(AuctionStatus::LIVE, $auction->status);
        $this->assertNotNull($auction->started_at);
        $this->assertNull($auction->completed_at);
    }

    public function test_paused_factory_state_sets_paused_status(): void
    {
        $auction = Auction::factory()->paused()->create();

        $this->assertSame(AuctionStatus::PAUSED, $auction->status);
        $this->assertNotNull($auction->started_at);
    }

    public function test_completed_factory_state_sets_completed_status_and_dates(): void
    {
        $auction = Auction::factory()->completed()->create();

        $this->assertSame(AuctionStatus::COMPLETED, $auction->status);
        $this->assertNotNull($auction->started_at);
        $this->assertNotNull($auction->completed_at);
    }

    public function test_cancelled_factory_state_sets_cancelled_status(): void
    {
        $auction = Auction::factory()->cancelled()->create();

        $this->assertSame(AuctionStatus::CANCELLED, $auction->status);
    }
}
