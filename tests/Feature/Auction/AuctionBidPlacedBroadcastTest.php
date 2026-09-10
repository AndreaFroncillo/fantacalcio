<?php

namespace Tests\Feature\Auction;

use App\Events\Auction\AuctionBidPlaced;
use App\Models\Auction\AuctionBid;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuctionBidPlacedBroadcastTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_is_broadcast_and_dispatched_after_commit(): void
    {
        $bid = AuctionBid::factory()->create();

        $event = new AuctionBidPlaced($bid);

        $this->assertInstanceOf(
            ShouldBroadcast::class,
            $event
        );

        $this->assertInstanceOf(
            ShouldDispatchAfterCommit::class,
            $event
        );
    }

    public function test_event_broadcasts_on_private_auction_channel(): void
    {
        $bid = AuctionBid::factory()->create();

        $event = new AuctionBidPlaced($bid);

        $channels = $event->broadcastOn();

        $this->assertCount(1, $channels);

        $this->assertSame(
            'private-auction.' . $bid->nomination->auction->ulid,
            $channels[0]->name
        );
    }

    public function test_event_has_stable_name_and_minimal_payload(): void
    {
        $bid = AuctionBid::factory()->create();

        $event = new AuctionBidPlaced($bid);

        $this->assertSame(
            'auction.bid.placed',
            $event->broadcastAs()
        );

        $this->assertSame([
            'auction_ulid' => $bid->nomination->auction->ulid,
            'nomination_ulid' => $bid->nomination->ulid,
            'bid_ulid' => $bid->ulid,
            'auction_participant_ulid' => $bid->participant->ulid,
            'amount' => $bid->amount,
            'sequence_number' => $bid->sequence_number,
            'placed_at' => $bid->placed_at?->toISOString(),
            'expires_at' => $bid->nomination->expires_at?->toISOString(),
        ], $event->broadcastWith());
    }
}
