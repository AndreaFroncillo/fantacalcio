<?php

namespace Tests\Feature\Auction\View;

use App\Models\Auction\Auction;
use App\Models\League\LeagueMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuctionPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_league_member_can_open_auction_page_by_ulid(): void
    {
        $auction = Auction::factory()->create();

        $user = User::factory()->create();

        LeagueMembership::factory()->create([
            'league_id' => $auction
                ->marketSession
                ->leagueSeason
                ->league_id,
            'user_id' => $user->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->get("/auctions/{$auction->ulid}");

        $response->assertOk();

        $response->assertViewIs('auction.show');

        $response->assertViewHas(
            'auction',
            fn(Auction $viewAuction) =>
            $viewAuction->is($auction)
        );
    }

    public function test_user_outside_league_cannot_open_auction_page(): void
    {
        $auction = Auction::factory()->create();

        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get("/auctions/{$auction->ulid}");

        $response->assertForbidden();
    }
}
