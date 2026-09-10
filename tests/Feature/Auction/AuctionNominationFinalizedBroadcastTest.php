<?php

namespace Tests\Feature\Auction;

use App\Domain\Auction\Enums\AuctionNominationCloseReason;
use App\Domain\Auction\Enums\AuctionNominationStatus;
use App\Events\Auction\AuctionNominationFinalized;
use App\Models\Auction\AuctionNomination;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuctionNominationFinalizedBroadcastTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_is_broadcast_and_dispatched_after_commit(): void
    {
        $nomination = AuctionNomination::factory()->create([
            'status' => AuctionNominationStatus::COMPLETED,
            'close_reason' => AuctionNominationCloseReason::TIMER_EXPIRED,
            'closed_at' => now(),
        ]);

        $event = new AuctionNominationFinalized($nomination);

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
        $nomination = AuctionNomination::factory()->create([
            'status' => AuctionNominationStatus::COMPLETED,
            'close_reason' => AuctionNominationCloseReason::TIMER_EXPIRED,
            'closed_at' => now(),
        ]);

        $event = new AuctionNominationFinalized($nomination);

        $channels = $event->broadcastOn();

        $this->assertCount(1, $channels);

        $this->assertSame(
            'private-auction.' . $nomination->auction->ulid,
            $channels[0]->name
        );
    }

    public function test_event_has_stable_name_and_minimal_payload(): void
    {
        $nomination = AuctionNomination::factory()->create([
            'status' => AuctionNominationStatus::COMPLETED,
            'close_reason' => AuctionNominationCloseReason::TIMER_EXPIRED,
            'closed_at' => now(),
        ]);

        $event = new AuctionNominationFinalized($nomination);

        $this->assertSame(
            'auction.nomination.finalized',
            $event->broadcastAs()
        );

        $this->assertSame([
            'auction_ulid' => $nomination->auction->ulid,
            'nomination_ulid' => $nomination->ulid,
            'status' => $nomination->status->value,
            'close_reason' => $nomination->close_reason->value,
            'closed_at' => $nomination->closed_at?->toISOString(),
        ], $event->broadcastWith());
    }
}
