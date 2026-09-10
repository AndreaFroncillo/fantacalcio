<?php

namespace Tests\Feature\Auction;

use App\Domain\Auction\Actions\InitializeAuction;
use App\Domain\Auction\Actions\PlaceAuctionBid;
use App\Domain\Auction\Actions\StartAuction;
use App\Domain\Auction\Actions\StartAuctionNomination;
use App\Domain\Auction\Enums\AuctionNominationStatus;
use App\Domain\Auction\Enums\AuctionStatus;
use App\Domain\Football\Enums\PlayerRole;
use App\Domain\Market\Enums\MarketCapabilityType;
use App\Domain\Market\Enums\MarketSessionStatus;
use App\Events\Auction\AuctionBidPlaced;
use App\Models\Auction\Auction;
use App\Models\Auction\AuctionNomination;
use App\Models\Auction\AuctionParticipant;
use App\Models\Credit\TeamCreditAccount;
use App\Models\Football\FootballSeason;
use App\Models\Football\PlayerSeason;
use App\Models\Market\MarketCapability;
use App\Models\Roster\LeagueSeasonRosterRule;
use App\Models\Season\SeasonParticipation;
use App\Models\Team\Team;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use RuntimeException;
use Tests\TestCase;

class PlaceAuctionBidTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([
            AuctionBidPlaced::class,
        ]);
    }

    public function test_it_places_the_first_bid_on_an_active_nomination(): void
    {
        $auction = $this->createStartedAuction();

        $leagueSeason = $auction->marketSession->leagueSeason;

        $footballSeason = FootballSeason::factory()->create([
            'start_year' => $leagueSeason->start_year,
            'end_year' => $leagueSeason->end_year,
            'name' => sprintf(
                'Serie A %d/%d',
                $leagueSeason->start_year,
                $leagueSeason->end_year
            ),
        ]);

        $playerSeason = PlayerSeason::factory()->create([
            'football_season_id' => $footballSeason->id,
            'role' => PlayerRole::GOALKEEPER,
        ]);

        $nomination = app(StartAuctionNomination::class)->execute(
            $auction,
            $playerSeason,
            $this->currentParticipantUser($auction)
        );

        $participant = $auction->participants()->firstOrFail();

        $bid = app(PlaceAuctionBid::class)->execute(
            $nomination,
            $participant,
            10
        );

        $this->assertSame($nomination->id, $bid->auction_nomination_id);

        $this->assertSame(
            $participant->id,
            $bid->auction_participant_id
        );

        $this->assertSame(10, $bid->amount);
        $this->assertSame(1, $bid->sequence_number);
        $this->assertNotNull($bid->placed_at);

        $this->assertDatabaseHas('auction_bids', [
            'auction_nomination_id' => $nomination->id,
            'auction_participant_id' => $participant->id,
            'amount' => 10,
            'sequence_number' => 1,
        ]);
    }

    private function createStartedAuction(): Auction
    {
        $auction = Auction::factory()->create([
            'base_timer_seconds' => 60,
            'bid_extension_seconds' => 10,
        ]);

        $rosterRules = [
            PlayerRole::GOALKEEPER->value => 3,
            PlayerRole::DEFENDER->value => 8,
            PlayerRole::MIDFIELDER->value => 8,
            PlayerRole::FORWARD->value => 6,
        ];

        foreach ($rosterRules as $role => $maxPlayers) {
            LeagueSeasonRosterRule::factory()->create([
                'league_season_id' => $auction->marketSession->league_season_id,
                'role' => $role,
                'max_players' => $maxPlayers,
            ]);
        }

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

        $team = Team::factory()->create([
            'season_participation_id' => $participation->id,
        ]);

        TeamCreditAccount::factory()->create([
            'team_id' => $team->id,
            'initial_balance' => 500,
            'current_balance' => 500,
        ]);

        $auction = app(InitializeAuction::class)->execute($auction);

        return app(StartAuction::class)->execute($auction);
    }

    public function test_it_places_a_second_bid_with_incremented_sequence_number(): void
    {
        $auction = $this->createStartedAuction();

        $leagueSeason = $auction->marketSession->leagueSeason;

        $footballSeason = FootballSeason::factory()->create([
            'start_year' => $leagueSeason->start_year,
            'end_year' => $leagueSeason->end_year,
            'name' => sprintf(
                'Serie A %d/%d',
                $leagueSeason->start_year,
                $leagueSeason->end_year
            ),
        ]);

        $firstParticipation = $auction->participants()
            ->firstOrFail()
            ->team
            ->seasonParticipation;

        $secondParticipation = SeasonParticipation::factory()->create([
            'league_season_id' => $leagueSeason->id,
        ]);

        $secondTeam = Team::factory()->create([
            'season_participation_id' => $secondParticipation->id,
        ]);

        TeamCreditAccount::factory()->create([
            'team_id' => $secondTeam->id,
            'initial_balance' => 500,
            'current_balance' => 500,
        ]);

        /*
     * L'asta è già stata inizializzata, quindi dobbiamo creare
     * manualmente il secondo partecipante coerente con l'ordine.
     */
        $secondParticipant = $auction->participants()->create([
            'team_id' => $secondTeam->id,
            'nomination_position' => 2,
        ]);

        $playerSeason = PlayerSeason::factory()->create([
            'football_season_id' => $footballSeason->id,
            'role' => PlayerRole::GOALKEEPER,
        ]);

        $nomination = app(StartAuctionNomination::class)->execute(
            $auction,
            $playerSeason,
            $this->currentParticipantUser($auction)
        );

        $firstParticipant = $auction->participants()
            ->where('nomination_position', 1)
            ->firstOrFail();

        app(PlaceAuctionBid::class)->execute(
            $nomination,
            $firstParticipant,
            10
        );

        $secondBid = app(PlaceAuctionBid::class)->execute(
            $nomination,
            $secondParticipant,
            15
        );

        $this->assertSame(2, $secondBid->sequence_number);
        $this->assertSame(15, $secondBid->amount);
        $this->assertSame(
            $secondParticipant->id,
            $secondBid->auction_participant_id
        );
    }

    public function test_it_fails_when_same_participant_bids_twice_consecutively(): void
    {
        $auction = $this->createStartedAuction();

        $leagueSeason = $auction->marketSession->leagueSeason;

        $footballSeason = FootballSeason::factory()->create([
            'start_year' => $leagueSeason->start_year,
            'end_year' => $leagueSeason->end_year,
            'name' => sprintf(
                'Serie A %d/%d',
                $leagueSeason->start_year,
                $leagueSeason->end_year
            ),
        ]);

        $playerSeason = PlayerSeason::factory()->create([
            'football_season_id' => $footballSeason->id,
            'role' => PlayerRole::GOALKEEPER,
        ]);

        $nomination = app(StartAuctionNomination::class)->execute(
            $auction,
            $playerSeason,
            $this->currentParticipantUser($auction)
        );

        $participant = $auction->participants()->firstOrFail();

        app(PlaceAuctionBid::class)->execute(
            $nomination,
            $participant,
            10
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Participant cannot bid twice consecutively.'
        );

        app(PlaceAuctionBid::class)->execute(
            $nomination,
            $participant,
            15
        );
    }

    public function test_it_fails_when_bid_is_not_greater_than_current_bid(): void
    {
        $auction = $this->createStartedAuction();

        $leagueSeason = $auction->marketSession->leagueSeason;

        $footballSeason = FootballSeason::factory()->create([
            'start_year' => $leagueSeason->start_year,
            'end_year' => $leagueSeason->end_year,
            'name' => sprintf(
                'Serie A %d/%d',
                $leagueSeason->start_year,
                $leagueSeason->end_year
            ),
        ]);

        $secondParticipation = SeasonParticipation::factory()->create([
            'league_season_id' => $leagueSeason->id,
        ]);

        $secondTeam = Team::factory()->create([
            'season_participation_id' => $secondParticipation->id,
        ]);

        TeamCreditAccount::factory()->create([
            'team_id' => $secondTeam->id,
            'initial_balance' => 500,
            'current_balance' => 500,
        ]);

        $secondParticipant = $auction->participants()->create([
            'team_id' => $secondTeam->id,
            'nomination_position' => 2,
        ]);

        $playerSeason = PlayerSeason::factory()->create([
            'football_season_id' => $footballSeason->id,
            'role' => PlayerRole::GOALKEEPER,
        ]);

        $nomination = app(StartAuctionNomination::class)->execute(
            $auction,
            $playerSeason,
            $this->currentParticipantUser($auction)
        );

        $firstParticipant = $auction->participants()
            ->where('nomination_position', 1)
            ->firstOrFail();

        app(PlaceAuctionBid::class)->execute(
            $nomination,
            $firstParticipant,
            10
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Bid amount must be greater than the current bid.'
        );

        app(PlaceAuctionBid::class)->execute(
            $nomination,
            $secondParticipant,
            10
        );
    }

    public function test_it_fails_when_bid_exceeds_participant_available_credits(): void
    {
        $auction = $this->createStartedAuction();

        $leagueSeason = $auction->marketSession->leagueSeason;

        $footballSeason = FootballSeason::factory()->create([
            'start_year' => $leagueSeason->start_year,
            'end_year' => $leagueSeason->end_year,
            'name' => sprintf(
                'Serie A %d/%d',
                $leagueSeason->start_year,
                $leagueSeason->end_year
            ),
        ]);

        $playerSeason = PlayerSeason::factory()->create([
            'football_season_id' => $footballSeason->id,
            'role' => PlayerRole::GOALKEEPER,
        ]);

        $nomination = app(StartAuctionNomination::class)->execute(
            $auction,
            $playerSeason,
            $this->currentParticipantUser($auction)
        );

        $participant = $auction->participants()
            ->with('team.creditAccount')
            ->firstOrFail();

        $participant->team->creditAccount->update([
            'current_balance' => 20,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Bid amount exceeds participant available credits.'
        );

        app(PlaceAuctionBid::class)->execute(
            $nomination,
            $participant,
            21
        );
    }

    public function test_it_fails_when_participant_does_not_belong_to_nomination_auction(): void
    {
        $auction = $this->createStartedAuction();

        $leagueSeason = $auction->marketSession->leagueSeason;

        $footballSeason = FootballSeason::factory()->create([
            'start_year' => $leagueSeason->start_year,
            'end_year' => $leagueSeason->end_year,
            'name' => sprintf(
                'Serie A %d/%d',
                $leagueSeason->start_year,
                $leagueSeason->end_year
            ),
        ]);

        $playerSeason = PlayerSeason::factory()->create([
            'football_season_id' => $footballSeason->id,
            'role' => PlayerRole::GOALKEEPER,
        ]);

        $nomination = app(StartAuctionNomination::class)->execute(
            $auction,
            $playerSeason,
            $this->currentParticipantUser($auction)
        );

        $otherAuction = $this->createStartedAuction();

        $otherParticipant = $otherAuction->participants()
            ->firstOrFail();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Participant does not belong to the nomination auction.'
        );

        app(PlaceAuctionBid::class)->execute(
            $nomination,
            $otherParticipant,
            10
        );
    }

    public function test_it_fails_when_nomination_is_not_active(): void
    {
        $auction = $this->createStartedAuction();

        $leagueSeason = $auction->marketSession->leagueSeason;

        $footballSeason = FootballSeason::factory()->create([
            'start_year' => $leagueSeason->start_year,
            'end_year' => $leagueSeason->end_year,
            'name' => sprintf(
                'Serie A %d/%d',
                $leagueSeason->start_year,
                $leagueSeason->end_year
            ),
        ]);

        $playerSeason = PlayerSeason::factory()->create([
            'football_season_id' => $footballSeason->id,
            'role' => PlayerRole::GOALKEEPER,
        ]);

        $nomination = app(StartAuctionNomination::class)->execute(
            $auction,
            $playerSeason,
            $this->currentParticipantUser($auction)
        );

        $nomination->update([
            'status' => AuctionNominationStatus::COMPLETED,
            'closed_at' => now(),
        ]);

        $participant = $auction->participants()->firstOrFail();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Auction nomination is not active.'
        );

        app(PlaceAuctionBid::class)->execute(
            $nomination,
            $participant,
            10
        );
    }

    public function test_it_fails_when_auction_is_not_live(): void
    {
        $auction = $this->createStartedAuction();

        $leagueSeason = $auction->marketSession->leagueSeason;

        $footballSeason = FootballSeason::factory()->create([
            'start_year' => $leagueSeason->start_year,
            'end_year' => $leagueSeason->end_year,
            'name' => sprintf(
                'Serie A %d/%d',
                $leagueSeason->start_year,
                $leagueSeason->end_year
            ),
        ]);

        $playerSeason = PlayerSeason::factory()->create([
            'football_season_id' => $footballSeason->id,
            'role' => PlayerRole::GOALKEEPER,
        ]);

        $nomination = app(StartAuctionNomination::class)->execute(
            $auction,
            $playerSeason,
            $this->currentParticipantUser($auction)
        );

        $auction->update([
            'status' => AuctionStatus::COMPLETED,
        ]);

        $participant = $auction->participants()->firstOrFail();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Auction is not live.'
        );

        app(PlaceAuctionBid::class)->execute(
            $nomination,
            $participant,
            10
        );
    }

    public function test_it_fails_when_first_bid_is_lower_than_opening_price(): void
    {
        $auction = $this->createStartedAuction();

        $leagueSeason = $auction->marketSession->leagueSeason;

        $footballSeason = FootballSeason::factory()->create([
            'start_year' => $leagueSeason->start_year,
            'end_year' => $leagueSeason->end_year,
            'name' => sprintf(
                'Serie A %d/%d',
                $leagueSeason->start_year,
                $leagueSeason->end_year
            ),
        ]);

        $playerSeason = PlayerSeason::factory()->create([
            'football_season_id' => $footballSeason->id,
            'role' => PlayerRole::GOALKEEPER,
        ]);

        $nomination = app(StartAuctionNomination::class)->execute(
            $auction,
            $playerSeason,
            $this->currentParticipantUser($auction)
        );

        $nomination->update([
            'opening_price' => 5,
        ]);

        $participant = $auction->participants()->firstOrFail();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Bid amount must be at least the opening price.'
        );

        app(PlaceAuctionBid::class)->execute(
            $nomination,
            $participant,
            4
        );
    }

    public function test_it_fails_when_nomination_timer_has_expired(): void
    {
        $auction = $this->createStartedAuction();

        $leagueSeason = $auction->marketSession->leagueSeason;

        $footballSeason = FootballSeason::factory()->create([
            'start_year' => $leagueSeason->start_year,
            'end_year' => $leagueSeason->end_year,
            'name' => sprintf(
                'Serie A %d/%d',
                $leagueSeason->start_year,
                $leagueSeason->end_year
            ),
        ]);

        $playerSeason = PlayerSeason::factory()->create([
            'football_season_id' => $footballSeason->id,
            'role' => PlayerRole::GOALKEEPER,
        ]);

        $nomination = app(StartAuctionNomination::class)->execute(
            $auction,
            $playerSeason,
            $this->currentParticipantUser($auction)
        );

        $nomination->update([
            'expires_at' => now()->subSecond(),
        ]);

        $participant = $auction->participants()->firstOrFail();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Auction nomination timer has expired.'
        );

        app(PlaceAuctionBid::class)->execute(
            $nomination,
            $participant,
            10
        );
    }

    public function test_it_extends_nomination_timer_after_a_valid_bid(): void
    {
        $startedAt = Carbon::parse('2026-09-08 10:00:00');

        Carbon::setTestNow($startedAt);

        try {
            $auction = $this->createStartedAuction();

            $leagueSeason = $auction->marketSession->leagueSeason;

            $footballSeason = FootballSeason::factory()->create([
                'start_year' => $leagueSeason->start_year,
                'end_year' => $leagueSeason->end_year,
                'name' => sprintf(
                    'Serie A %d/%d',
                    $leagueSeason->start_year,
                    $leagueSeason->end_year
                ),
            ]);

            $playerSeason = PlayerSeason::factory()->create([
                'football_season_id' => $footballSeason->id,
                'role' => PlayerRole::GOALKEEPER,
            ]);

            $nomination = app(StartAuctionNomination::class)->execute(
                $auction,
                $playerSeason,
                $this->currentParticipantUser($auction)
            );

            Carbon::setTestNow(
                $startedAt->copy()->addSeconds(20)
            );

            $participant = $auction->participants()->firstOrFail();

            app(PlaceAuctionBid::class)->execute(
                $nomination,
                $participant,
                10
            );

            $nomination->refresh();

            $this->assertTrue(
                $nomination->expires_at->equalTo(
                    $startedAt->copy()->addSeconds(70)
                )
            );
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_it_does_not_extend_nomination_timer_beyond_base_timer_seconds(): void
    {
        $startedAt = Carbon::parse('2026-09-08 10:00:00');

        Carbon::setTestNow($startedAt);

        try {
            $auction = $this->createStartedAuction();

            $leagueSeason = $auction->marketSession->leagueSeason;

            $footballSeason = FootballSeason::factory()->create([
                'start_year' => $leagueSeason->start_year,
                'end_year' => $leagueSeason->end_year,
                'name' => sprintf(
                    'Serie A %d/%d',
                    $leagueSeason->start_year,
                    $leagueSeason->end_year
                ),
            ]);

            $playerSeason = PlayerSeason::factory()->create([
                'football_season_id' => $footballSeason->id,
                'role' => PlayerRole::GOALKEEPER,
            ]);

            $nomination = app(StartAuctionNomination::class)->execute(
                $auction,
                $playerSeason,
                $this->currentParticipantUser($auction)
            );

            Carbon::setTestNow(
                $startedAt->copy()->addSeconds(5)
            );

            $participant = $auction->participants()->firstOrFail();

            app(PlaceAuctionBid::class)->execute(
                $nomination,
                $participant,
                10
            );

            $nomination->refresh();

            $this->assertTrue(
                $nomination->expires_at->equalTo(
                    $startedAt->copy()->addSeconds(65)
                )
            );
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_it_does_not_deduct_credits_when_bid_is_placed(): void
    {
        $auction = $this->createStartedAuction();

        $leagueSeason = $auction->marketSession->leagueSeason;

        $footballSeason = FootballSeason::factory()->create([
            'start_year' => $leagueSeason->start_year,
            'end_year' => $leagueSeason->end_year,
            'name' => sprintf(
                'Serie A %d/%d',
                $leagueSeason->start_year,
                $leagueSeason->end_year
            ),
        ]);

        $playerSeason = PlayerSeason::factory()->create([
            'football_season_id' => $footballSeason->id,
            'role' => PlayerRole::GOALKEEPER,
        ]);

        $nomination = app(StartAuctionNomination::class)->execute(
            $auction,
            $playerSeason,
            $this->currentParticipantUser($auction)
        );

        $participant = $auction->participants()
            ->with('team.creditAccount')
            ->firstOrFail();

        $creditAccount = $participant->team->creditAccount;

        $this->assertSame(500, $creditAccount->current_balance);

        app(PlaceAuctionBid::class)->execute(
            $nomination,
            $participant,
            100
        );

        $creditAccount->refresh();

        $this->assertSame(
            500,
            $creditAccount->current_balance
        );
    }

    public function test_it_fails_when_participant_team_has_no_credit_account(): void
    {
        $auction = $this->createStartedAuction();

        $leagueSeason = $auction->marketSession->leagueSeason;

        $footballSeason = FootballSeason::factory()->create([
            'start_year' => $leagueSeason->start_year,
            'end_year' => $leagueSeason->end_year,
            'name' => sprintf(
                'Serie A %d/%d',
                $leagueSeason->start_year,
                $leagueSeason->end_year
            ),
        ]);

        $playerSeason = PlayerSeason::factory()->create([
            'football_season_id' => $footballSeason->id,
            'role' => PlayerRole::GOALKEEPER,
        ]);

        $nomination = app(StartAuctionNomination::class)->execute(
            $auction,
            $playerSeason,
            $this->currentParticipantUser($auction)
        );

        $participant = $auction->participants()
            ->with('team.creditAccount')
            ->firstOrFail();

        $participant->team->creditAccount()->delete();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Auction participant team has no credit account.'
        );

        app(PlaceAuctionBid::class)->execute(
            $nomination,
            $participant,
            10
        );
    }

    public function test_it_fails_when_bid_is_placed_exactly_at_expiration(): void
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
        ]);

        $participant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Auction nomination timer has expired.'
        );

        app(PlaceAuctionBid::class)->execute(
            $nomination,
            $participant,
            1
        );
    }

    public function test_it_dispatches_realtime_event_when_bid_is_placed(): void
    {
        $auction = $this->createStartedAuction();

        $leagueSeason = $auction->marketSession->leagueSeason;

        $footballSeason = FootballSeason::factory()->create([
            'start_year' => $leagueSeason->start_year,
            'end_year' => $leagueSeason->end_year,
            'name' => sprintf(
                'Serie A %d/%d',
                $leagueSeason->start_year,
                $leagueSeason->end_year
            ),
        ]);

        $playerSeason = PlayerSeason::factory()->create([
            'football_season_id' => $footballSeason->id,
            'role' => PlayerRole::GOALKEEPER,
        ]);

        $nomination = app(StartAuctionNomination::class)->execute(
            $auction,
            $playerSeason,
            $this->currentParticipantUser($auction)
        );

        $participant = $auction->participants()->firstOrFail();

        $bid = app(PlaceAuctionBid::class)->execute(
            $nomination,
            $participant,
            10
        );

        Event::assertDispatched(
            AuctionBidPlaced::class,
            function (AuctionBidPlaced $event) use ($bid) {
                return $event->bid->is($bid);
            }
        );
    }

    private function currentParticipantUser(Auction $auction): User
    {
        return $auction->participants()
            ->orderBy('nomination_position')
            ->firstOrFail()
            ->team
            ->seasonParticipation
            ->leagueMembership
            ->user;
    }
}
