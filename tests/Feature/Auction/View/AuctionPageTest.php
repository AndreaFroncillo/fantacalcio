<?php

namespace Tests\Feature\Auction\View;

use App\Models\Auction\Auction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuctionPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_auction_page_can_be_opened_by_ulid(): void
    {
        $auction = Auction::factory()->create();

        $response = $this->get(
            "/auctions/{$auction->ulid}"
        );

        $response->assertOk();

        $response->assertViewIs('auction.show');

        $response->assertViewHas(
            'auction',
            fn(Auction $viewAuction) =>
            $viewAuction->is($auction)
        );
    }
}
