<?php

namespace Tests\Feature\Market;

use App\Domain\Market\Enums\MarketCapabilityEventType;
use App\Models\Market\MarketCapability;
use App\Models\Market\MarketCapabilityEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketCapabilityEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_market_capability_event_can_be_created_from_factory(): void
    {
        $event = MarketCapabilityEvent::factory()->create();

        $this->assertDatabaseHas('market_capability_events', [
            'id' => $event->id,
            'market_capability_id' => $event->market_capability_id,
        ]);
    }

    public function test_ulid_is_generated_automatically(): void
    {
        $event = MarketCapabilityEvent::factory()->create();

        $this->assertNotNull($event->ulid);
        $this->assertSame(26, strlen($event->ulid));
    }

    public function test_type_is_cast_to_enum(): void
    {
        $event = MarketCapabilityEvent::factory()->create();

        $this->assertSame(
            MarketCapabilityEventType::ENABLED,
            $event->type
        );
    }

    public function test_disabled_factory_state_sets_disabled_type(): void
    {
        $event = MarketCapabilityEvent::factory()
            ->disabled()
            ->create();

        $this->assertSame(
            MarketCapabilityEventType::DISABLED,
            $event->type
        );
    }

    public function test_market_capability_event_belongs_to_market_capability(): void
    {
        $capability = MarketCapability::factory()->create();

        $event = MarketCapabilityEvent::factory()->create([
            'market_capability_id' => $capability->id,
        ]);

        $this->assertTrue(
            $event->marketCapability->is($capability)
        );
    }

    public function test_market_capability_has_events(): void
    {
        $capability = MarketCapability::factory()->create();

        $event = MarketCapabilityEvent::factory()->create([
            'market_capability_id' => $capability->id,
        ]);

        $this->assertTrue(
            $capability->events->contains($event)
        );
    }

    public function test_event_can_belong_to_user(): void
    {
        $user = User::factory()->create();

        $event = MarketCapabilityEvent::factory()->create([
            'performed_by_user_id' => $user->id,
        ]);

        $this->assertTrue(
            $event->performedBy->is($user)
        );
    }

    public function test_system_event_can_have_no_user(): void
    {
        $event = MarketCapabilityEvent::factory()
            ->system()
            ->create();

        $this->assertNull($event->performed_by_user_id);
        $this->assertNull($event->performedBy);
    }

    public function test_reason_can_be_null(): void
    {
        $event = MarketCapabilityEvent::factory()->create([
            'reason' => null,
        ]);

        $this->assertNull($event->reason);
    }

    public function test_user_deletion_keeps_event_and_sets_actor_to_null(): void
    {
        $user = User::factory()->create();

        $event = MarketCapabilityEvent::factory()->create([
            'performed_by_user_id' => $user->id,
        ]);

        $user->delete();

        $event->refresh();

        $this->assertNull($event->performed_by_user_id);
        $this->assertDatabaseHas('market_capability_events', [
            'id' => $event->id,
        ]);
    }
}
