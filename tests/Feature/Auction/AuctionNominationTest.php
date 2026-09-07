<?php

namespace Tests\Feature\Auction;

use App\Domain\Auction\Enums\AuctionNominationCloseReason;
use App\Domain\Auction\Enums\AuctionNominationStatus;
use App\Models\Auction\Auction;
use App\Models\Auction\AuctionNomination;
use App\Models\Auction\AuctionParticipant;
use App\Models\Auction\AuctionRolePhase;
use App\Models\Football\PlayerSeason;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuctionNominationTest extends TestCase
{
    use RefreshDatabase;

    public function test_auction_nomination_can_be_created_from_factory(): void
    {
        $nomination = AuctionNomination::factory()->create();

        $this->assertDatabaseHas('auction_nominations', [
            'id' => $nomination->id,
            'status' => AuctionNominationStatus::ACTIVE->value,
            'opening_price' => 1,
        ]);
    }

    public function test_ulid_is_generated_automatically(): void
    {
        $nomination = AuctionNomination::factory()->create();

        $this->assertNotNull($nomination->ulid);
        $this->assertSame(26, strlen($nomination->ulid));
    }

    public function test_status_and_close_reason_are_cast_to_enums(): void
    {
        $nomination = AuctionNomination::factory()
            ->completedByTimer()
            ->create();

        $this->assertSame(
            AuctionNominationStatus::COMPLETED,
            $nomination->status
        );

        $this->assertSame(
            AuctionNominationCloseReason::TIMER_EXPIRED,
            $nomination->close_reason
        );
    }

    public function test_numeric_fields_are_cast_to_integer(): void
    {
        $nomination = AuctionNomination::factory()->create([
            'turn_number' => 7,
            'opening_price' => 5,
        ]);

        $this->assertSame(7, $nomination->turn_number);
        $this->assertSame(5, $nomination->opening_price);
    }

    public function test_dates_are_cast_to_datetime(): void
    {
        $nomination = AuctionNomination::factory()
            ->completedByTimer()
            ->create();

        $this->assertNotNull($nomination->timer_started_at);
        $this->assertNotNull($nomination->expires_at);
        $this->assertNotNull($nomination->closed_at);
    }

    public function test_closing_fields_can_be_null_for_active_nomination(): void
    {
        $nomination = AuctionNomination::factory()->create();

        $this->assertNull($nomination->closed_at);
        $this->assertNull($nomination->closed_by_user_id);
        $this->assertNull($nomination->close_reason);
    }

    public function test_nomination_belongs_to_auction(): void
    {
        $nomination = AuctionNomination::factory()->create();

        $this->assertTrue($nomination->auction->is(
            Auction::find($nomination->auction_id)
        ));
    }

    public function test_nomination_belongs_to_role_phase(): void
    {
        $nomination = AuctionNomination::factory()->create();

        $this->assertTrue($nomination->rolePhase->is(
            AuctionRolePhase::find($nomination->auction_role_phase_id)
        ));
    }

    public function test_nomination_belongs_to_participant(): void
    {
        $nomination = AuctionNomination::factory()->create();

        $this->assertTrue($nomination->participant->is(
            AuctionParticipant::find($nomination->auction_participant_id)
        ));
    }

    public function test_nomination_belongs_to_player_season(): void
    {
        $nomination = AuctionNomination::factory()->create();

        $this->assertTrue($nomination->playerSeason->is(
            PlayerSeason::find($nomination->player_season_id)
        ));
    }

    public function test_auction_has_nominations_ordered_by_turn_number(): void
    {
        $auction = Auction::factory()->create();
        $playerSeason = PlayerSeason::factory()->create();

        $third = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'player_season_id' => $playerSeason->id,
            'turn_number' => 3,
        ]);

        $first = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'player_season_id' => $playerSeason->id,
            'turn_number' => 1,
        ]);

        $second = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'player_season_id' => $playerSeason->id,
            'turn_number' => 2,
        ]);

        $this->assertSame(
            [$first->id, $second->id, $third->id],
            $auction->nominations->pluck('id')->all()
        );
    }

    public function test_inverse_relationships_return_nominations(): void
    {
        $nomination = AuctionNomination::factory()->create();

        $this->assertTrue(
            $nomination->rolePhase->nominations->contains($nomination)
        );

        $this->assertTrue(
            $nomination->participant->nominations->contains($nomination)
        );

        $this->assertTrue(
            $nomination->playerSeason->auctionNominations->contains($nomination)
        );
    }

    public function test_completed_by_timer_factory_state(): void
    {
        $nomination = AuctionNomination::factory()
            ->completedByTimer()
            ->create();

        $this->assertSame(
            AuctionNominationStatus::COMPLETED,
            $nomination->status
        );
        $this->assertSame(
            AuctionNominationCloseReason::TIMER_EXPIRED,
            $nomination->close_reason
        );
        $this->assertNull($nomination->closed_by_user_id);
        $this->assertNotNull($nomination->closed_at);
    }

    public function test_confirmed_by_president_factory_state(): void
    {
        $nomination = AuctionNomination::factory()
            ->confirmedByPresident()
            ->create();

        $this->assertSame(
            AuctionNominationStatus::COMPLETED,
            $nomination->status
        );
        $this->assertSame(
            AuctionNominationCloseReason::PRESIDENT_CONFIRMED,
            $nomination->close_reason
        );
        $this->assertNotNull($nomination->closed_by_user_id);
    }

    public function test_rejected_by_president_factory_state(): void
    {
        $nomination = AuctionNomination::factory()
            ->rejectedByPresident()
            ->create();

        $this->assertSame(
            AuctionNominationStatus::REJECTED,
            $nomination->status
        );
        $this->assertSame(
            AuctionNominationCloseReason::PRESIDENT_REJECTED,
            $nomination->close_reason
        );
        $this->assertNotNull($nomination->closed_by_user_id);
    }

    public function test_closed_by_user_relationship(): void
    {
        $user = User::factory()->create();

        $nomination = AuctionNomination::factory()->create([
            'status' => AuctionNominationStatus::COMPLETED,
            'closed_at' => now(),
            'closed_by_user_id' => $user->id,
            'close_reason' => AuctionNominationCloseReason::PRESIDENT_CONFIRMED,
        ]);

        $this->assertTrue($nomination->closedByUser->is($user));
    }

    public function test_user_deletion_keeps_nomination_and_sets_closed_by_to_null(): void
    {
        $user = User::factory()->create();

        $nomination = AuctionNomination::factory()->create([
            'status' => AuctionNominationStatus::COMPLETED,
            'closed_at' => now(),
            'closed_by_user_id' => $user->id,
            'close_reason' => AuctionNominationCloseReason::PRESIDENT_CONFIRMED,
        ]);

        $user->delete();

        $nomination->refresh();

        $this->assertNull($nomination->closed_by_user_id);

        $this->assertDatabaseHas('auction_nominations', [
            'id' => $nomination->id,
        ]);
    }

    public function test_same_turn_number_cannot_have_two_nominations_in_same_auction(): void
    {
        $auction = Auction::factory()->create();

        AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'turn_number' => 5,
        ]);

        $this->expectException(QueryException::class);

        AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'turn_number' => 5,
        ]);
    }

    public function test_same_turn_number_can_exist_in_different_auctions(): void
    {
        $first = AuctionNomination::factory()->create([
            'turn_number' => 5,
        ]);

        $second = AuctionNomination::factory()->create([
            'turn_number' => 5,
        ]);

        $this->assertNotSame(
            $first->auction_id,
            $second->auction_id
        );
    }
}
