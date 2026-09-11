<?php

namespace Tests\Feature\Auction;

use App\Domain\Auction\Enums\AuctionRolePhaseStatus;
use App\Domain\Auction\Enums\AuctionStatus;
use App\Domain\Football\Enums\PlayerRole;
use App\Events\Auction\AuctionStarted;
use App\Models\Auction\Auction;
use App\Models\Auction\AuctionRolePhase;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuctionStartedBroadcastTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_is_broadcast_and_dispatched_after_commit(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        $event = new AuctionStarted($auction);

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
            ->live()
            ->create();

        $event = new AuctionStarted($auction);

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
            ->live()
            ->create();

        $phase = AuctionRolePhase::factory()
            ->create([
                'auction_id' => $auction->id,
                'role' => PlayerRole::GOALKEEPER,
                'position' => 1,
                'status' => AuctionRolePhaseStatus::ACTIVE,
                'started_at' => $auction->started_at,
            ]);

        $auction->load('rolePhases');

        $event = new AuctionStarted($auction);

        $this->assertSame(
            'auction.started',
            $event->broadcastAs()
        );

        $this->assertSame([
            'auction_ulid' => $auction->ulid,
            'status' => AuctionStatus::LIVE->value,
            'started_at' => $auction->started_at?->toISOString(),
            'active_role_phase_ulid' => $phase->ulid,
            'active_role' => PlayerRole::GOALKEEPER->value,
        ], $event->broadcastWith());
    }
}
