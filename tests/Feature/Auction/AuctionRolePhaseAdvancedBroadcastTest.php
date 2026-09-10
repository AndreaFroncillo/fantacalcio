<?php

namespace Tests\Feature\Auction;

use App\Domain\Football\Enums\PlayerRole;
use App\Events\Auction\AuctionRolePhaseAdvanced;
use App\Models\Auction\AuctionRolePhase;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuctionRolePhaseAdvancedBroadcastTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_is_broadcast_and_dispatched_after_commit(): void
    {
        $previousPhase = AuctionRolePhase::factory()
            ->completed()
            ->create();

        $nextPhase = AuctionRolePhase::factory()
            ->active()
            ->create([
                'auction_id' => $previousPhase->auction_id,
                'role' => PlayerRole::DEFENDER,
                'position' => $previousPhase->position + 1,
            ]);

        $event = new AuctionRolePhaseAdvanced(
            $previousPhase,
            $nextPhase
        );

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
        $previousPhase = AuctionRolePhase::factory()
            ->completed()
            ->create();

        $nextPhase = AuctionRolePhase::factory()
            ->active()
            ->create([
                'auction_id' => $previousPhase->auction_id,
                'role' => PlayerRole::DEFENDER,
                'position' => $previousPhase->position + 1,
            ]);

        $event = new AuctionRolePhaseAdvanced(
            $previousPhase,
            $nextPhase
        );

        $channels = $event->broadcastOn();

        $this->assertCount(1, $channels);

        $this->assertSame(
            'private-auction.' . $nextPhase->auction->ulid,
            $channels[0]->name
        );
    }

    public function test_event_has_stable_name_and_minimal_payload(): void
    {
        $previousPhase = AuctionRolePhase::factory()
            ->completed()
            ->create([
                'role' => PlayerRole::GOALKEEPER,
                'position' => 1,
            ]);

        $nextPhase = AuctionRolePhase::factory()
            ->active()
            ->create([
                'auction_id' => $previousPhase->auction_id,
                'role' => PlayerRole::DEFENDER,
                'position' => 2,
            ]);

        $event = new AuctionRolePhaseAdvanced(
            $previousPhase,
            $nextPhase
        );

        $this->assertSame(
            'auction.role-phase.advanced',
            $event->broadcastAs()
        );

        $this->assertSame([
            'auction_ulid' => $nextPhase->auction->ulid,
            'previous_phase_ulid' => $previousPhase->ulid,
            'previous_role' => PlayerRole::GOALKEEPER->value,
            'next_phase_ulid' => $nextPhase->ulid,
            'next_role' => PlayerRole::DEFENDER->value,
            'started_at' => $nextPhase->started_at?->toISOString(),
        ], $event->broadcastWith());
    }
}
