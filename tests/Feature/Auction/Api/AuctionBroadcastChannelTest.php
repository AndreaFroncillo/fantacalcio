<?php

namespace Tests\Feature\Auction\Api;

use App\Models\Auction\Auction;
use App\Models\League\LeagueMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuctionBroadcastChannelTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_league_member_can_authorize_auction_channel(): void
    {
        $user = User::factory()->create();

        $auction = Auction::factory()->create();

        LeagueMembership::factory()->create([
            'league_id' => $auction->marketSession->leagueSeason->league_id,
            'user_id' => $user->id,
        ]);

        $response = $this
            ->actingAs($user, 'sanctum')
            ->postJson('/api/broadcasting/auth', [
                'channel_name' => 'private-auction.' . $auction->ulid,
                'socket_id' => '1234.5678',
            ]);

        $response->assertOk();
    }

    public function test_user_outside_league_cannot_authorize_auction_channel(): void
    {
        $user = User::factory()->create();

        $auction = Auction::factory()->create();

        $response = $this
            ->actingAs($user, 'sanctum')
            ->postJson('/api/broadcasting/auth', [
                'channel_name' => 'private-auction.' . $auction->ulid,
                'socket_id' => '1234.5678',
            ]);

        $response->assertForbidden();
    }

    public function test_inactive_league_member_cannot_authorize_auction_channel(): void
    {
        $user = User::factory()->create();

        $auction = Auction::factory()->create();

        LeagueMembership::factory()
            ->inactive()
            ->create([
                'league_id' => $auction->marketSession->leagueSeason->league_id,
                'user_id' => $user->id,
            ]);

        $response = $this
            ->actingAs($user, 'sanctum')
            ->postJson('/api/broadcasting/auth', [
                'channel_name' => 'private-auction.' . $auction->ulid,
                'socket_id' => '1234.5678',
            ]);

        $response->assertForbidden();
    }

    public function test_unauthenticated_user_cannot_authorize_auction_channel(): void
    {
        $auction = Auction::factory()->create();

        $response = $this->postJson('/api/broadcasting/auth', [
            'channel_name' => 'private-auction.' . $auction->ulid,
            'socket_id' => '1234.5678',
        ]);

        $response->assertUnauthorized();
    }
}
