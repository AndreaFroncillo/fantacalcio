<?php

namespace Tests\Feature\Auction;

use App\Models\Auction\AuctionBid;
use App\Models\Auction\AuctionNomination;
use App\Models\Auction\AuctionParticipant;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuctionBidTest extends TestCase
{
    use RefreshDatabase;

    public function test_auction_bid_can_be_created_from_factory(): void
    {
        $bid = AuctionBid::factory()->create();

        $this->assertDatabaseHas('auction_bids', [
            'id' => $bid->id,
            'amount' => 1,
            'sequence_number' => 1,
        ]);
    }

    public function test_ulid_is_generated_automatically(): void
    {
        $bid = AuctionBid::factory()->create();

        $this->assertNotNull($bid->ulid);
        $this->assertSame(26, strlen($bid->ulid));
    }

    public function test_numeric_fields_are_cast_to_integer(): void
    {
        $bid = AuctionBid::factory()->create([
            'amount' => 17,
            'sequence_number' => 4,
        ]);

        $this->assertSame(17, $bid->amount);
        $this->assertSame(4, $bid->sequence_number);
    }

    public function test_placed_at_is_cast_to_datetime(): void
    {
        $bid = AuctionBid::factory()->create();

        $this->assertNotNull($bid->placed_at);
    }

    public function test_bid_belongs_to_nomination(): void
    {
        $bid = AuctionBid::factory()->create();

        $this->assertTrue($bid->nomination->is(
            AuctionNomination::find($bid->auction_nomination_id)
        ));
    }

    public function test_bid_belongs_to_participant(): void
    {
        $bid = AuctionBid::factory()->create();

        $this->assertTrue($bid->participant->is(
            AuctionParticipant::find($bid->auction_participant_id)
        ));
    }

    public function test_nomination_has_bids_ordered_by_sequence_number(): void
    {
        $nomination = AuctionNomination::factory()->create();

        $third = AuctionBid::factory()->create([
            'auction_nomination_id' => $nomination->id,
            'sequence_number' => 3,
            'amount' => 10,
        ]);

        $first = AuctionBid::factory()->create([
            'auction_nomination_id' => $nomination->id,
            'sequence_number' => 1,
            'amount' => 1,
        ]);

        $second = AuctionBid::factory()->create([
            'auction_nomination_id' => $nomination->id,
            'sequence_number' => 2,
            'amount' => 5,
        ]);

        $this->assertSame(
            [$first->id, $second->id, $third->id],
            $nomination->bids->pluck('id')->all()
        );
    }

    public function test_participant_has_bids(): void
    {
        $participant = AuctionParticipant::factory()->create();

        $bid = AuctionBid::factory()->create([
            'auction_participant_id' => $participant->id,
        ]);

        $this->assertTrue(
            $participant->bids->contains($bid)
        );
    }

    public function test_same_sequence_number_cannot_exist_twice_in_same_nomination(): void
    {
        $nomination = AuctionNomination::factory()->create();

        AuctionBid::factory()->create([
            'auction_nomination_id' => $nomination->id,
            'sequence_number' => 2,
        ]);

        $this->expectException(QueryException::class);

        AuctionBid::factory()->create([
            'auction_nomination_id' => $nomination->id,
            'sequence_number' => 2,
        ]);
    }

    public function test_same_sequence_number_can_exist_in_different_nominations(): void
    {
        $first = AuctionBid::factory()->create([
            'sequence_number' => 2,
        ]);

        $second = AuctionBid::factory()->create([
            'sequence_number' => 2,
        ]);

        $this->assertNotSame(
            $first->auction_nomination_id,
            $second->auction_nomination_id
        );
    }

    public function test_bid_amount_is_stored_as_absolute_total(): void
    {
        $bid = AuctionBid::factory()->create([
            'amount' => 17,
        ]);

        $this->assertSame(17, $bid->amount);
    }
}
