<?php

namespace Tests\Feature\Auction;

use App\Events\Auction\AuctionCompleted;
use App\Models\Auction\Auction;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuctionCompletedBroadcastTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_is_broadcast_and_dispatched_after_commit(): void
    {
        $auction = Auction::factory()
            ->completed()
            ->create();

        $event = new AuctionCompleted($auction);

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
        $auction = Auction::factory()
            ->completed()
            ->create();

        $event = new AuctionCompleted($auction);

        $channels = $event->broadcastOn();

        $this->assertCount(1, $channels);

        $this->assertInstanceOf(
            PrivateChannel::class,
            $channels[0]
        );

        $this->assertSame(
            'private-auction.' . $auction->ulid,
            $channels[0]->name
        );
    }

    public function test_event_has_stable_name_and_minimal_payload(): void
    {
        $auction = Auction::factory()
            ->completed()
            ->create();

        $event = new AuctionCompleted($auction);

        $this->assertSame(
            'auction.completed',
            $event->broadcastAs()
        );

        $this->assertSame([
            'auction_ulid' => $auction->ulid,
            'status' => $auction->status->value,
            'completed_at' => $auction->completed_at?->toISOString(),
        ], $event->broadcastWith());
    }
}
