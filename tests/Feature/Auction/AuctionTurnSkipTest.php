<?php

namespace Tests\Feature\Auction;

use App\Domain\Auction\Enums\AuctionTurnSkipReason;
use App\Models\Auction\Auction;
use App\Models\Auction\AuctionParticipant;
use App\Models\Auction\AuctionRolePhase;
use App\Models\Auction\AuctionTurnSkip;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuctionTurnSkipTest extends TestCase
{
    use RefreshDatabase;

    public function test_auction_turn_skip_can_be_created_from_factory(): void
    {
        $skip = AuctionTurnSkip::factory()->create();

        $this->assertDatabaseHas('auction_turn_skips', [
            'id' => $skip->id,
            'auction_id' => $skip->auction_id,
            'auction_role_phase_id' => $skip->auction_role_phase_id,
            'auction_participant_id' => $skip->auction_participant_id,
        ]);
    }

    public function test_ulid_is_generated_automatically(): void
    {
        $skip = AuctionTurnSkip::factory()->create();

        $this->assertNotNull($skip->ulid);
        $this->assertSame(26, strlen($skip->ulid));
    }

    public function test_reason_is_cast_to_enum(): void
    {
        $skip = AuctionTurnSkip::factory()->create();

        $this->assertSame(
            AuctionTurnSkipReason::ROLE_COMPLETE,
            $skip->reason
        );
    }

    public function test_turn_number_is_cast_to_integer(): void
    {
        $skip = AuctionTurnSkip::factory()->create([
            'turn_number' => 7,
        ]);

        $this->assertSame(7, $skip->turn_number);
    }

    public function test_auction_turn_skip_belongs_to_auction(): void
    {
        $skip = AuctionTurnSkip::factory()->create();

        $this->assertTrue($skip->auction->is(
            Auction::find($skip->auction_id)
        ));
    }

    public function test_auction_turn_skip_belongs_to_role_phase(): void
    {
        $skip = AuctionTurnSkip::factory()->create();

        $this->assertTrue($skip->rolePhase->is(
            AuctionRolePhase::find($skip->auction_role_phase_id)
        ));
    }

    public function test_auction_turn_skip_belongs_to_participant(): void
    {
        $skip = AuctionTurnSkip::factory()->create();

        $this->assertTrue($skip->participant->is(
            AuctionParticipant::find($skip->auction_participant_id)
        ));
    }

    public function test_auction_has_turn_skips_ordered_by_turn_number(): void
    {
        $auction = Auction::factory()->create();

        $third = AuctionTurnSkip::factory()->create([
            'auction_id' => $auction->id,
            'turn_number' => 3,
        ]);

        $first = AuctionTurnSkip::factory()->create([
            'auction_id' => $auction->id,
            'turn_number' => 1,
        ]);

        $second = AuctionTurnSkip::factory()->create([
            'auction_id' => $auction->id,
            'turn_number' => 2,
        ]);

        $this->assertSame(
            [$first->id, $second->id, $third->id],
            $auction->turnSkips->pluck('id')->all()
        );
    }

    public function test_role_phase_has_turn_skips(): void
    {
        $skip = AuctionTurnSkip::factory()->create();

        $this->assertTrue(
            $skip->rolePhase->turnSkips->contains($skip)
        );
    }

    public function test_participant_has_turn_skips(): void
    {
        $skip = AuctionTurnSkip::factory()->create();

        $this->assertTrue(
            $skip->participant->turnSkips->contains($skip)
        );
    }

    public function test_system_skip_can_have_no_user(): void
    {
        $skip = AuctionTurnSkip::factory()->create([
            'performed_by_user_id' => null,
        ]);

        $this->assertNull($skip->performedByUser);
    }

    public function test_president_override_can_belong_to_user(): void
    {
        $user = User::factory()->create();

        $skip = AuctionTurnSkip::factory()->create([
            'reason' => AuctionTurnSkipReason::PRESIDENT_OVERRIDE,
            'performed_by_user_id' => $user->id,
        ]);

        $this->assertTrue($skip->performedByUser->is($user));
    }

    public function test_user_deletion_keeps_skip_and_sets_actor_to_null(): void
    {
        $user = User::factory()->create();

        $skip = AuctionTurnSkip::factory()->create([
            'reason' => AuctionTurnSkipReason::PRESIDENT_OVERRIDE,
            'performed_by_user_id' => $user->id,
        ]);

        $user->delete();

        $skip->refresh();

        $this->assertNull($skip->performed_by_user_id);

        $this->assertDatabaseHas('auction_turn_skips', [
            'id' => $skip->id,
        ]);
    }

    public function test_note_can_be_null(): void
    {
        $skip = AuctionTurnSkip::factory()->create([
            'note' => null,
        ]);

        $this->assertNull($skip->note);
    }

    public function test_same_turn_number_cannot_have_two_skips_in_same_auction(): void
    {
        $auction = Auction::factory()->create();

        AuctionTurnSkip::factory()->create([
            'auction_id' => $auction->id,
            'turn_number' => 5,
        ]);

        $this->expectException(QueryException::class);

        AuctionTurnSkip::factory()->create([
            'auction_id' => $auction->id,
            'turn_number' => 5,
        ]);
    }

    public function test_same_turn_number_can_exist_in_different_auctions(): void
    {
        $first = AuctionTurnSkip::factory()->create([
            'turn_number' => 5,
        ]);

        $second = AuctionTurnSkip::factory()->create([
            'turn_number' => 5,
        ]);

        $this->assertNotSame(
            $first->auction_id,
            $second->auction_id
        );
    }
}
