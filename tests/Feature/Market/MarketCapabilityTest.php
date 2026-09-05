<?php

namespace Tests\Feature\Market;

use App\Domain\Market\Enums\MarketCapabilityType;
use App\Models\Market\MarketCapability;
use App\Models\Market\MarketSession;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketCapabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_market_capability_can_be_created_from_factory(): void
    {
        $capability = MarketCapability::factory()->create();

        $this->assertDatabaseHas('market_capabilities', [
            'id' => $capability->id,
            'market_session_id' => $capability->market_session_id,
        ]);
    }

    public function test_ulid_is_generated_automatically(): void
    {
        $capability = MarketCapability::factory()->create();

        $this->assertNotNull($capability->ulid);
        $this->assertSame(26, strlen($capability->ulid));
    }

    public function test_type_is_cast_to_enum(): void
    {
        $capability = MarketCapability::factory()->create();

        $this->assertSame(
            MarketCapabilityType::AUCTION,
            $capability->type
        );
    }

    public function test_is_enabled_is_cast_to_boolean(): void
    {
        $capability = MarketCapability::factory()->create([
            'is_enabled' => false,
        ]);

        $this->assertFalse($capability->is_enabled);
        $this->assertIsBool($capability->is_enabled);
    }

    public function test_enabled_factory_state_enables_capability(): void
    {
        $capability = MarketCapability::factory()->enabled()->create();

        $this->assertTrue($capability->is_enabled);
    }

    public function test_market_capability_belongs_to_market_session(): void
    {
        $marketSession = MarketSession::factory()->create();

        $capability = MarketCapability::factory()->create([
            'market_session_id' => $marketSession->id,
        ]);

        $this->assertTrue(
            $capability->marketSession->is($marketSession)
        );
    }

    public function test_market_session_has_capabilities(): void
    {
        $marketSession = MarketSession::factory()->create();

        $capability = MarketCapability::factory()->create([
            'market_session_id' => $marketSession->id,
        ]);

        $this->assertTrue(
            $marketSession->capabilities->contains($capability)
        );
    }

    public function test_same_type_cannot_exist_twice_in_same_market_session(): void
    {
        $marketSession = MarketSession::factory()->create();

        MarketCapability::factory()->create([
            'market_session_id' => $marketSession->id,
            'type' => MarketCapabilityType::AUCTION,
        ]);

        $this->expectException(QueryException::class);

        MarketCapability::factory()->create([
            'market_session_id' => $marketSession->id,
            'type' => MarketCapabilityType::AUCTION,
        ]);
    }

    public function test_same_type_can_exist_in_different_market_sessions(): void
    {
        $first = MarketCapability::factory()->create([
            'type' => MarketCapabilityType::AUCTION,
        ]);

        $second = MarketCapability::factory()->create([
            'type' => MarketCapabilityType::AUCTION,
        ]);

        $this->assertNotSame(
            $first->market_session_id,
            $second->market_session_id
        );
    }
}
