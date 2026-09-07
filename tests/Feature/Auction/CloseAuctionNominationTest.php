<?php

namespace Tests\Feature\Auction;

use App\Domain\Auction\Actions\CloseAuctionNomination;
use App\Domain\Auction\Enums\AuctionNominationCloseReason;
use App\Domain\Auction\Enums\AuctionNominationStatus;
use App\Models\Auction\Auction;
use App\Models\Auction\AuctionBid;
use App\Models\Auction\AuctionNomination;
use App\Models\Auction\AuctionParticipant;
use App\Models\Credit\TeamCreditAccount;
use App\Models\League\LeagueMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class CloseAuctionNominationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_closes_an_expired_nomination_by_timer(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'status' => AuctionNominationStatus::ACTIVE,
            'expires_at' => now()->subSecond(),
            'closed_at' => null,
            'closed_by_user_id' => null,
            'close_reason' => null,
        ]);

        $nomination = app(CloseAuctionNomination::class)->execute(
            $nomination,
            AuctionNominationCloseReason::TIMER_EXPIRED
        );

        $this->assertSame(
            AuctionNominationStatus::COMPLETED,
            $nomination->status
        );

        $this->assertSame(
            AuctionNominationCloseReason::TIMER_EXPIRED,
            $nomination->close_reason
        );

        $this->assertNotNull($nomination->closed_at);
        $this->assertNull($nomination->closed_by_user_id);

        $this->assertDatabaseHas('auction_nominations', [
            'id' => $nomination->id,
            'status' => AuctionNominationStatus::COMPLETED->value,
            'close_reason' => AuctionNominationCloseReason::TIMER_EXPIRED->value,
            'closed_by_user_id' => null,
        ]);
    }

    public function test_it_fails_when_timer_expired_reason_is_used_before_expiration(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'status' => AuctionNominationStatus::ACTIVE,
            'expires_at' => now()->addMinute(),
            'closed_at' => null,
            'closed_by_user_id' => null,
            'close_reason' => null,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Auction nomination timer has not expired.'
        );

        app(CloseAuctionNomination::class)->execute(
            $nomination,
            AuctionNominationCloseReason::TIMER_EXPIRED
        );
    }

    public function test_it_fails_when_nomination_is_already_closed(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'status' => AuctionNominationStatus::COMPLETED,
            'expires_at' => now()->subSecond(),
            'closed_at' => now(),
            'closed_by_user_id' => null,
            'close_reason' => AuctionNominationCloseReason::TIMER_EXPIRED,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Auction nomination is not active.'
        );

        app(CloseAuctionNomination::class)->execute(
            $nomination,
            AuctionNominationCloseReason::TIMER_EXPIRED
        );
    }

    public function test_it_closes_active_nomination_when_president_confirms(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'status' => AuctionNominationStatus::ACTIVE,
            'expires_at' => now()->addMinute(),
            'closed_at' => null,
            'closed_by_user_id' => null,
            'close_reason' => null,
        ]);

        $president = User::factory()->create();

        LeagueMembership::factory()
            ->president()
            ->create([
                'league_id' => $auction->marketSession->leagueSeason->league_id,
                'user_id' => $president->id,
            ]);

        $nomination = app(CloseAuctionNomination::class)->execute(
            $nomination,
            AuctionNominationCloseReason::PRESIDENT_CONFIRMED,
            $president
        );

        $this->assertSame(
            AuctionNominationStatus::COMPLETED,
            $nomination->status
        );

        $this->assertSame(
            AuctionNominationCloseReason::PRESIDENT_CONFIRMED,
            $nomination->close_reason
        );

        $this->assertSame(
            $president->id,
            $nomination->closed_by_user_id
        );

        $this->assertNotNull($nomination->closed_at);

        $this->assertDatabaseHas('auction_nominations', [
            'id' => $nomination->id,
            'status' => AuctionNominationStatus::COMPLETED->value,
            'close_reason' => AuctionNominationCloseReason::PRESIDENT_CONFIRMED->value,
            'closed_by_user_id' => $president->id,
        ]);
    }

    public function test_it_fails_when_president_confirmation_has_no_actor(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'status' => AuctionNominationStatus::ACTIVE,
            'expires_at' => now()->addMinute(),
            'closed_at' => null,
            'closed_by_user_id' => null,
            'close_reason' => null,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'President action requires an actor.'
        );

        app(CloseAuctionNomination::class)->execute(
            $nomination,
            AuctionNominationCloseReason::PRESIDENT_CONFIRMED
        );
    }

    public function test_it_fails_when_actor_is_not_league_president(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'status' => AuctionNominationStatus::ACTIVE,
            'expires_at' => now()->addMinute(),
            'closed_at' => null,
            'closed_by_user_id' => null,
            'close_reason' => null,
        ]);

        $member = User::factory()->create();

        LeagueMembership::factory()->create([
            'league_id' => $auction->marketSession->leagueSeason->league_id,
            'user_id' => $member->id,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Actor is not the league president.'
        );

        app(CloseAuctionNomination::class)->execute(
            $nomination,
            AuctionNominationCloseReason::PRESIDENT_CONFIRMED,
            $member
        );
    }

    public function test_it_rejects_active_nomination_when_president_rejects(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'status' => AuctionNominationStatus::ACTIVE,
            'expires_at' => now()->addMinute(),
            'closed_at' => null,
            'closed_by_user_id' => null,
            'close_reason' => null,
        ]);

        $president = User::factory()->create();

        LeagueMembership::factory()
            ->president()
            ->create([
                'league_id' => $auction->marketSession->leagueSeason->league_id,
                'user_id' => $president->id,
            ]);

        $nomination = app(CloseAuctionNomination::class)->execute(
            $nomination,
            AuctionNominationCloseReason::PRESIDENT_REJECTED,
            $president
        );

        $this->assertSame(
            AuctionNominationStatus::REJECTED,
            $nomination->status
        );

        $this->assertSame(
            AuctionNominationCloseReason::PRESIDENT_REJECTED,
            $nomination->close_reason
        );

        $this->assertSame(
            $president->id,
            $nomination->closed_by_user_id
        );

        $this->assertNotNull($nomination->closed_at);

        $this->assertDatabaseHas('auction_nominations', [
            'id' => $nomination->id,
            'status' => AuctionNominationStatus::REJECTED->value,
            'close_reason' => AuctionNominationCloseReason::PRESIDENT_REJECTED->value,
            'closed_by_user_id' => $president->id,
        ]);
    }

    public function test_it_fails_when_president_rejection_has_no_actor(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'status' => AuctionNominationStatus::ACTIVE,
            'expires_at' => now()->addMinute(),
            'closed_at' => null,
            'closed_by_user_id' => null,
            'close_reason' => null,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'President action requires an actor.'
        );

        app(CloseAuctionNomination::class)->execute(
            $nomination,
            AuctionNominationCloseReason::PRESIDENT_REJECTED
        );
    }

    public function test_it_fails_when_rejection_actor_is_not_league_president(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'status' => AuctionNominationStatus::ACTIVE,
            'expires_at' => now()->addMinute(),
            'closed_at' => null,
            'closed_by_user_id' => null,
            'close_reason' => null,
        ]);

        $member = User::factory()->create();

        LeagueMembership::factory()->create([
            'league_id' => $auction->marketSession->leagueSeason->league_id,
            'user_id' => $member->id,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Actor is not the league president.'
        );

        app(CloseAuctionNomination::class)->execute(
            $nomination,
            AuctionNominationCloseReason::PRESIDENT_REJECTED,
            $member
        );
    }

    public function test_it_fails_when_auction_is_not_live(): void
    {
        $auction = Auction::factory()->create();

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'status' => AuctionNominationStatus::ACTIVE,
            'expires_at' => now()->subSecond(),
            'closed_at' => null,
            'closed_by_user_id' => null,
            'close_reason' => null,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Auction is not live.'
        );

        app(CloseAuctionNomination::class)->execute(
            $nomination,
            AuctionNominationCloseReason::TIMER_EXPIRED
        );
    }

    public function test_it_fails_when_timer_expiration_has_an_actor(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'status' => AuctionNominationStatus::ACTIVE,
            'expires_at' => now()->subSecond(),
            'closed_at' => null,
            'closed_by_user_id' => null,
            'close_reason' => null,
        ]);

        $actor = User::factory()->create();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Timer expiration cannot have an actor.'
        );

        app(CloseAuctionNomination::class)->execute(
            $nomination,
            AuctionNominationCloseReason::TIMER_EXPIRED,
            $actor
        );
    }

    public function test_it_closes_nomination_when_timer_expires_exactly_now(): void
    {
        $now = now()->startOfSecond();

        $this->travelTo($now);

        $auction = Auction::factory()
            ->live()
            ->create();

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'status' => AuctionNominationStatus::ACTIVE,
            'expires_at' => $now,
            'closed_at' => null,
            'closed_by_user_id' => null,
            'close_reason' => null,
        ]);

        $nomination = app(CloseAuctionNomination::class)->execute(
            $nomination,
            AuctionNominationCloseReason::TIMER_EXPIRED
        );

        $this->assertSame(
            AuctionNominationStatus::COMPLETED,
            $nomination->status
        );

        $this->assertSame(
            AuctionNominationCloseReason::TIMER_EXPIRED,
            $nomination->close_reason
        );
    }

    public function test_it_does_not_acquire_player_or_deduct_credits_when_nomination_is_closed(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'status' => AuctionNominationStatus::ACTIVE,
            'expires_at' => now()->subSecond(),
            'closed_at' => null,
            'closed_by_user_id' => null,
            'close_reason' => null,
        ]);

        $participant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
        ]);

        $creditAccount = TeamCreditAccount::factory()->create([
            'team_id' => $participant->team_id,
        ]);

        $initialBalance = $creditAccount->current_balance;

        AuctionBid::factory()->create([
            'auction_nomination_id' => $nomination->id,
            'auction_participant_id' => $participant->id,
            'amount' => 10,
            'sequence_number' => 1,
            'placed_at' => now()->subSeconds(10),
        ]);

        app(CloseAuctionNomination::class)->execute(
            $nomination,
            AuctionNominationCloseReason::TIMER_EXPIRED
        );

        $this->assertDatabaseMissing('roster_ownerships', [
            'league_season_id' => $auction->marketSession->league_season_id,
            'player_season_id' => $nomination->player_season_id,
        ]);

        $this->assertSame(
            $initialBalance,
            $creditAccount->fresh()->current_balance
        );
    }
}
