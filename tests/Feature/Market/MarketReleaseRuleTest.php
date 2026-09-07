<?php

namespace Tests\Feature\Market;

use App\Models\Market\MarketReleaseRule;
use App\Models\Market\MarketSession;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketReleaseRuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_market_release_rule_can_be_created_from_factory(): void
    {
        $rule = MarketReleaseRule::factory()->create();

        $this->assertDatabaseHas('market_release_rules', [
            'id' => $rule->id,
            'market_session_id' => $rule->market_session_id,
        ]);
    }

    public function test_ulid_is_generated_automatically(): void
    {
        $rule = MarketReleaseRule::factory()->create();

        $this->assertNotNull($rule->ulid);
        $this->assertSame(26, strlen($rule->ulid));
    }

    public function test_max_releases_per_team_is_cast_to_integer(): void
    {
        $rule = MarketReleaseRule::factory()->create([
            'max_releases_per_team' => 3,
        ]);

        $this->assertSame(3, $rule->max_releases_per_team);
    }

    public function test_max_releases_per_team_can_be_null(): void
    {
        $rule = MarketReleaseRule::factory()
            ->unlimited()
            ->create();

        $this->assertNull($rule->max_releases_per_team);
    }

    public function test_market_release_rule_belongs_to_market_session(): void
    {
        $session = MarketSession::factory()->create();

        $rule = MarketReleaseRule::factory()->create([
            'market_session_id' => $session->id,
        ]);

        $this->assertTrue(
            $rule->marketSession->is($session)
        );
    }

    public function test_market_session_has_release_rule(): void
    {
        $session = MarketSession::factory()->create();

        $rule = MarketReleaseRule::factory()->create([
            'market_session_id' => $session->id,
        ]);

        $this->assertTrue(
            $session->releaseRule->is($rule)
        );
    }

    public function test_market_session_cannot_have_two_release_rules(): void
    {
        $session = MarketSession::factory()->create();

        MarketReleaseRule::factory()->create([
            'market_session_id' => $session->id,
        ]);

        $this->expectException(QueryException::class);

        MarketReleaseRule::factory()->create([
            'market_session_id' => $session->id,
        ]);
    }

    public function test_different_market_sessions_can_have_release_rules(): void
    {
        $firstSession = MarketSession::factory()->create();
        $secondSession = MarketSession::factory()->create();

        $firstRule = MarketReleaseRule::factory()->create([
            'market_session_id' => $firstSession->id,
        ]);

        $secondRule = MarketReleaseRule::factory()->create([
            'market_session_id' => $secondSession->id,
        ]);

        $this->assertNotSame(
            $firstRule->market_session_id,
            $secondRule->market_session_id
        );
    }
}
