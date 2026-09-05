<?php

namespace Tests\Feature\Market;

use App\Domain\Market\Enums\MarketSessionStatus;
use App\Models\Market\MarketSession;
use App\Models\Season\LeagueSeason;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class MarketSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_market_session_can_be_created_from_factory(): void
    {
        $marketSession = MarketSession::factory()->create();

        $this->assertDatabaseHas('market_sessions', [
            'id' => $marketSession->id,
            'league_season_id' => $marketSession->league_season_id,
        ]);
    }

    public function test_ulid_is_generated_automatically(): void
    {
        $marketSession = MarketSession::factory()->create();

        $this->assertNotNull($marketSession->ulid);
        $this->assertSame(26, strlen($marketSession->ulid));
    }

    public function test_status_is_cast_to_enum(): void
    {
        $marketSession = MarketSession::factory()->create();

        $this->assertSame(
            MarketSessionStatus::SCHEDULED,
            $marketSession->status
        );
    }

    public function test_dates_are_cast_to_datetime(): void
    {
        $marketSession = MarketSession::factory()->create([
            'starts_at' => now(),
            'ends_at' => now()->addDay(),
        ]);

        $this->assertInstanceOf(Carbon::class, $marketSession->starts_at);
        $this->assertInstanceOf(Carbon::class, $marketSession->ends_at);
    }

    public function test_dates_can_be_null(): void
    {
        $marketSession = MarketSession::factory()->create([
            'starts_at' => null,
            'ends_at' => null,
        ]);

        $this->assertNull($marketSession->starts_at);
        $this->assertNull($marketSession->ends_at);
    }

    public function test_market_session_belongs_to_league_season(): void
    {
        $leagueSeason = LeagueSeason::factory()->create();

        $marketSession = MarketSession::factory()->create([
            'league_season_id' => $leagueSeason->id,
        ]);

        $this->assertTrue(
            $marketSession->leagueSeason->is($leagueSeason)
        );
    }

    public function test_league_season_has_market_sessions(): void
    {
        $leagueSeason = LeagueSeason::factory()->create();

        $marketSession = MarketSession::factory()->create([
            'league_season_id' => $leagueSeason->id,
        ]);

        $this->assertTrue(
            $leagueSeason->marketSessions->contains($marketSession)
        );
    }

    public function test_open_factory_state_sets_open_status(): void
    {
        $marketSession = MarketSession::factory()->open()->create();

        $this->assertSame(
            MarketSessionStatus::OPEN,
            $marketSession->status
        );
    }

    public function test_closed_factory_state_sets_closed_status(): void
    {
        $marketSession = MarketSession::factory()->closed()->create();

        $this->assertSame(
            MarketSessionStatus::CLOSED,
            $marketSession->status
        );
    }

    public function test_cancelled_factory_state_sets_cancelled_status(): void
    {
        $marketSession = MarketSession::factory()->cancelled()->create();

        $this->assertSame(
            MarketSessionStatus::CANCELLED,
            $marketSession->status
        );
    }
}
