<?php

namespace Tests\Feature\Auction;

use App\Models\Auction\Auction;
use App\Models\Auction\AuctionParticipant;
use App\Models\Team\Team;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuctionParticipantTest extends TestCase
{
    use RefreshDatabase;

    public function test_auction_participant_can_be_created_from_factory(): void
    {
        $participant = AuctionParticipant::factory()->create();

        $this->assertDatabaseHas('auction_participants', [
            'id' => $participant->id,
            'auction_id' => $participant->auction_id,
            'team_id' => $participant->team_id,
        ]);
    }

    public function test_ulid_is_generated_automatically(): void
    {
        $participant = AuctionParticipant::factory()->create();

        $this->assertNotNull($participant->ulid);
        $this->assertSame(26, strlen($participant->ulid));
    }

    public function test_nomination_position_is_cast_to_integer(): void
    {
        $participant = AuctionParticipant::factory()->create([
            'nomination_position' => 3,
        ]);

        $this->assertSame(3, $participant->nomination_position);
    }

    public function test_auction_participant_belongs_to_auction(): void
    {
        $participant = AuctionParticipant::factory()->create();

        $this->assertTrue(
            $participant->auction->is(
                Auction::find($participant->auction_id)
            )
        );
    }

    public function test_auction_participant_belongs_to_team(): void
    {
        $participant = AuctionParticipant::factory()->create();

        $this->assertTrue(
            $participant->team->is(
                Team::find($participant->team_id)
            )
        );
    }

    public function test_auction_has_participants_ordered_by_nomination_position(): void
    {
        $auction = Auction::factory()->create();

        $third = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'nomination_position' => 3,
        ]);

        $first = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'nomination_position' => 1,
        ]);

        $second = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'nomination_position' => 2,
        ]);

        $this->assertSame(
            [
                $first->id,
                $second->id,
                $third->id,
            ],
            $auction->participants->pluck('id')->all()
        );
    }

    public function test_team_has_auction_participations(): void
    {
        $team = Team::factory()->create();

        $participant = AuctionParticipant::factory()->create([
            'team_id' => $team->id,
        ]);

        $this->assertTrue(
            $team->auctionParticipations->contains($participant)
        );
    }

    public function test_same_team_cannot_participate_twice_in_same_auction(): void
    {
        $auction = Auction::factory()->create();
        $team = Team::factory()->create();

        AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'team_id' => $team->id,
            'nomination_position' => 1,
        ]);

        $this->expectException(QueryException::class);

        AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'team_id' => $team->id,
            'nomination_position' => 2,
        ]);
    }

    public function test_same_nomination_position_cannot_exist_twice_in_same_auction(): void
    {
        $auction = Auction::factory()->create();

        AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'nomination_position' => 1,
        ]);

        $this->expectException(QueryException::class);

        AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'nomination_position' => 1,
        ]);
    }

    public function test_same_team_can_participate_in_different_auctions(): void
    {
        $team = Team::factory()->create();

        AuctionParticipant::factory()->create([
            'team_id' => $team->id,
            'nomination_position' => 1,
        ]);

        $participant = AuctionParticipant::factory()->create([
            'team_id' => $team->id,
            'nomination_position' => 1,
        ]);

        $this->assertDatabaseHas('auction_participants', [
            'id' => $participant->id,
        ]);
    }
}
