<?php

namespace Tests\Feature\Auction;

use App\Domain\Auction\Actions\InitializeAuction;
use App\Domain\Auction\Actions\StartAuction;
use App\Domain\Auction\Enums\AuctionRolePhaseStatus;
use App\Domain\Auction\Enums\AuctionStatus;
use App\Domain\Market\Enums\MarketCapabilityType;
use App\Domain\Market\Enums\MarketSessionStatus;
use App\Models\Auction\Auction;
use App\Models\Market\MarketCapability;
use App\Models\Season\SeasonParticipation;
use App\Models\Team\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class StartAuctionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_starts_an_initialized_auction(): void
    {
        $auction = $this->createInitializedAuction();

        $result = app(StartAuction::class)->execute($auction);

        $this->assertSame(
            AuctionStatus::LIVE,
            $result->status
        );

        $this->assertNotNull($result->started_at);
    }

    public function test_it_activates_only_the_first_role_phase(): void
    {
        $auction = $this->createInitializedAuction();

        $result = app(StartAuction::class)->execute($auction);

        $phases = $result->rolePhases->values();

        $this->assertSame(
            AuctionRolePhaseStatus::ACTIVE,
            $phases[0]->status
        );

        $this->assertNotNull(
            $phases[0]->started_at
        );

        foreach ($phases->slice(1) as $phase) {
            $this->assertSame(
                AuctionRolePhaseStatus::PENDING,
                $phase->status
            );

            $this->assertNull(
                $phase->started_at
            );
        }
    }

    public function test_it_does_not_create_nominations_bids_or_turn_skips(): void
    {
        $auction = $this->createInitializedAuction();

        app(StartAuction::class)->execute($auction);

        $this->assertDatabaseCount('auction_nominations', 0);
        $this->assertDatabaseCount('auction_bids', 0);
        $this->assertDatabaseCount('auction_turn_skips', 0);
    }

    private function createInitializedAuction(): Auction
    {
        $auction = Auction::factory()->create();

        $auction->marketSession->update([
            'status' => MarketSessionStatus::OPEN,
        ]);

        MarketCapability::factory()->create([
            'market_session_id' => $auction->market_session_id,
            'type' => MarketCapabilityType::AUCTION,
            'is_enabled' => true,
        ]);

        $participation = SeasonParticipation::factory()->create([
            'league_season_id' => $auction->marketSession->league_season_id,
        ]);

        Team::factory()->create([
            'season_participation_id' => $participation->id,
        ]);

        return app(InitializeAuction::class)->execute($auction);
    }

    public function test_it_fails_when_auction_is_not_scheduled(): void
    {
        $auction = $this->createInitializedAuction();

        $auction->update([
            'status' => AuctionStatus::LIVE,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Only scheduled auctions can be started.'
        );

        app(StartAuction::class)->execute($auction);
    }

    public function test_it_fails_when_market_session_is_not_open(): void
    {
        $auction = $this->createInitializedAuction();

        $auction->marketSession->update([
            'status' => MarketSessionStatus::SCHEDULED,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Market session must be open before starting the auction.'
        );

        app(StartAuction::class)->execute($auction);
    }

    public function test_it_fails_when_auction_capability_is_missing(): void
    {
        $auction = Auction::factory()->create();

        $auction->marketSession->update([
            'status' => MarketSessionStatus::OPEN,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Auction capability is not enabled.'
        );

        app(StartAuction::class)->execute($auction);
    }

    public function test_it_fails_when_auction_capability_is_disabled(): void
    {
        $auction = Auction::factory()->create();

        $auction->marketSession->update([
            'status' => MarketSessionStatus::OPEN,
        ]);

        MarketCapability::factory()->create([
            'market_session_id' => $auction->market_session_id,
            'type' => MarketCapabilityType::AUCTION,
            'is_enabled' => false,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Auction capability is not enabled.'
        );

        app(StartAuction::class)->execute($auction);
    }

    public function test_it_fails_when_auction_has_no_participants(): void
    {
        $auction = Auction::factory()->create();

        $auction->marketSession->update([
            'status' => MarketSessionStatus::OPEN,
        ]);

        MarketCapability::factory()->create([
            'market_session_id' => $auction->market_session_id,
            'type' => MarketCapabilityType::AUCTION,
            'is_enabled' => true,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Auction has no participants.'
        );

        app(StartAuction::class)->execute($auction);
    }

    public function test_it_cannot_start_the_same_auction_twice(): void
    {
        $auction = $this->createInitializedAuction();

        $action = app(StartAuction::class);

        $action->execute($auction);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Only scheduled auctions can be started.'
        );

        $action->execute($auction);
    }

    public function test_it_fails_when_role_phases_are_incomplete(): void
    {
        $auction = $this->createInitializedAuction();

        $auction->rolePhases()
            ->where('position', 4)
            ->delete();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Auction role phases are not correctly initialized.'
        );

        app(StartAuction::class)->execute($auction);
    }

    public function test_it_fails_when_role_phases_are_not_all_pending(): void
    {
        $auction = $this->createInitializedAuction();

        $auction->rolePhases()
            ->where('position', 1)
            ->update([
                'status' => AuctionRolePhaseStatus::ACTIVE,
            ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Auction role phases are not correctly initialized.'
        );

        app(StartAuction::class)->execute($auction);
    }
}
