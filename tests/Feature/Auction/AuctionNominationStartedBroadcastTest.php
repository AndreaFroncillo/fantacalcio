<?php

namespace Tests\Feature\Auction;

use App\Events\Auction\AuctionNominationStarted;
use App\Models\Auction\AuctionNomination;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuctionNominationStartedBroadcastTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_is_broadcast_and_dispatched_after_commit(): void
    {
        $nomination = AuctionNomination::factory()->create();

        $event = new AuctionNominationStarted($nomination);

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
        $nomination = AuctionNomination::factory()->create();

        $event = new AuctionNominationStarted($nomination);

        $channels = $event->broadcastOn();

        $this->assertCount(1, $channels);

        $this->assertSame(
            'private-auction.' . $nomination->auction->ulid,
            $channels[0]->name
        );
    }

    public function test_event_has_stable_name_and_minimal_payload(): void
    {
        $nomination = AuctionNomination::factory()->create();

        $event = new AuctionNominationStarted($nomination);

        $this->assertSame(
            'auction.nomination.started',
            $event->broadcastAs()
        );

        $this->assertSame([
            'auction_ulid' => $nomination->auction->ulid,
            'nomination_ulid' => $nomination->ulid,
            'player_season_ulid' => $nomination->playerSeason->ulid,
            'auction_participant_ulid' => $nomination->participant->ulid,
            'turn_number' => $nomination->turn_number,
            'opening_price' => $nomination->opening_price,
            'timer_started_at' => $nomination->timer_started_at?->toISOString(),
            'expires_at' => $nomination->expires_at?->toISOString(),
        ], $event->broadcastWith());
    }
}
