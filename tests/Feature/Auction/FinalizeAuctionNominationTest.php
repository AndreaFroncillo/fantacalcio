<?php

namespace Tests\Feature\Auction;

use App\Domain\Auction\Actions\FinalizeAuctionNomination;
use App\Domain\Auction\Enums\AuctionNominationCloseReason;
use App\Domain\Auction\Enums\AuctionNominationStatus;
use App\Models\Auction\Auction;
use App\Models\Auction\AuctionBid;
use App\Models\Auction\AuctionNomination;
use App\Models\Auction\AuctionParticipant;
use App\Models\Credit\TeamCreditAccount;
use App\Models\League\LeagueMembership;
use App\Models\Roster\LeagueSeasonRosterRule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class FinalizeAuctionNominationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_closes_expired_nomination_and_acquires_player_for_winner(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'status' => AuctionNominationStatus::ACTIVE,
            'close_reason' => null,
            'closed_at' => null,
            'expires_at' => now()->subSecond(),
        ]);

        LeagueSeasonRosterRule::factory()->create([
            'league_season_id' => $auction
                ->marketSession
                ->league_season_id,
            'role' => $nomination->playerSeason->role,
            'max_players' => 3,
        ]);

        $participant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
        ]);

        TeamCreditAccount::factory()->create([
            'team_id' => $participant->team_id,
            'current_balance' => 100,
        ]);

        AuctionBid::factory()->create([
            'auction_nomination_id' => $nomination->id,
            'auction_participant_id' => $participant->id,
            'amount' => 10,
            'sequence_number' => 1,
            'placed_at' => now(),
        ]);

        $ownership = app(FinalizeAuctionNomination::class)
            ->execute(
                $nomination,
                AuctionNominationCloseReason::TIMER_EXPIRED
            );

        $this->assertSame(
            AuctionNominationStatus::COMPLETED,
            $nomination->fresh()->status
        );

        $this->assertSame(
            AuctionNominationCloseReason::TIMER_EXPIRED,
            $nomination->fresh()->close_reason
        );

        $this->assertNotNull(
            $nomination->fresh()->closed_at
        );

        $this->assertNotNull($ownership);

        $this->assertSame(
            $participant->team_id,
            $ownership->team_id
        );

        $this->assertSame(
            $nomination->player_season_id,
            $ownership->player_season_id
        );

        $this->assertSame(
            10,
            $ownership->acquisition_value
        );

        $this->assertSame(
            90,
            $participant
                ->team
                ->creditAccount
                ->fresh()
                ->current_balance
        );
    }

    public function test_it_rejects_nomination_without_acquiring_player_or_changing_credits(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'status' => AuctionNominationStatus::ACTIVE,
            'close_reason' => null,
            'closed_at' => null,
        ]);

        $participant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
        ]);

        $creditAccount = TeamCreditAccount::factory()->create([
            'team_id' => $participant->team_id,
            'current_balance' => 100,
        ]);

        AuctionBid::factory()->create([
            'auction_nomination_id' => $nomination->id,
            'auction_participant_id' => $participant->id,
            'amount' => 10,
            'sequence_number' => 1,
            'placed_at' => now(),
        ]);

        $league = $auction
            ->marketSession
            ->leagueSeason
            ->league;

        $president = User::factory()->create();

        LeagueMembership::factory()
            ->president()
            ->create([
                'league_id' => $league->id,
                'user_id' => $president->id,
            ]);

        $ownership = app(FinalizeAuctionNomination::class)
            ->execute(
                $nomination,
                AuctionNominationCloseReason::PRESIDENT_REJECTED,
                $president
            );

        $this->assertNull($ownership);

        $this->assertSame(
            AuctionNominationStatus::REJECTED,
            $nomination->fresh()->status
        );

        $this->assertSame(
            AuctionNominationCloseReason::PRESIDENT_REJECTED,
            $nomination->fresh()->close_reason
        );

        $this->assertSame(
            $president->id,
            $nomination->fresh()->closed_by_user_id
        );

        $this->assertSame(
            100,
            $creditAccount->fresh()->current_balance
        );

        $this->assertDatabaseMissing('roster_ownerships', [
            'league_season_id' => $auction
                ->marketSession
                ->league_season_id,
            'player_season_id' => $nomination->player_season_id,
        ]);
    }

    public function test_it_closes_expired_nomination_without_bids_as_unsold(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'status' => AuctionNominationStatus::ACTIVE,
            'close_reason' => null,
            'closed_at' => null,
            'expires_at' => now()->subSecond(),
        ]);

        $ownership = app(FinalizeAuctionNomination::class)
            ->execute(
                $nomination,
                AuctionNominationCloseReason::TIMER_EXPIRED
            );

        $this->assertNull($ownership);

        $this->assertSame(
            AuctionNominationStatus::COMPLETED,
            $nomination->fresh()->status
        );

        $this->assertSame(
            AuctionNominationCloseReason::TIMER_EXPIRED,
            $nomination->fresh()->close_reason
        );

        $this->assertNotNull(
            $nomination->fresh()->closed_at
        );

        $this->assertDatabaseMissing('roster_ownerships', [
            'league_season_id' => $auction
                ->marketSession
                ->league_season_id,
            'player_season_id' => $nomination->player_season_id,
        ]);
    }

    public function test_it_confirms_nomination_and_acquires_player_for_winner(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'status' => AuctionNominationStatus::ACTIVE,
            'close_reason' => null,
            'closed_at' => null,
            'expires_at' => now()->addMinute(),
        ]);

        LeagueSeasonRosterRule::factory()->create([
            'league_season_id' => $auction
                ->marketSession
                ->league_season_id,
            'role' => $nomination->playerSeason->role,
            'max_players' => 3,
        ]);

        $participant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
        ]);

        TeamCreditAccount::factory()->create([
            'team_id' => $participant->team_id,
            'current_balance' => 100,
        ]);

        AuctionBid::factory()->create([
            'auction_nomination_id' => $nomination->id,
            'auction_participant_id' => $participant->id,
            'amount' => 25,
            'sequence_number' => 1,
            'placed_at' => now(),
        ]);

        $league = $auction
            ->marketSession
            ->leagueSeason
            ->league;

        $president = User::factory()->create();

        LeagueMembership::factory()
            ->president()
            ->create([
                'league_id' => $league->id,
                'user_id' => $president->id,
            ]);

        $ownership = app(FinalizeAuctionNomination::class)
            ->execute(
                $nomination,
                AuctionNominationCloseReason::PRESIDENT_CONFIRMED,
                $president
            );

        $this->assertSame(
            AuctionNominationStatus::COMPLETED,
            $nomination->fresh()->status
        );

        $this->assertSame(
            AuctionNominationCloseReason::PRESIDENT_CONFIRMED,
            $nomination->fresh()->close_reason
        );

        $this->assertSame(
            $president->id,
            $nomination->fresh()->closed_by_user_id
        );

        $this->assertNotNull($ownership);

        $this->assertSame(
            $participant->team_id,
            $ownership->team_id
        );

        $this->assertSame(
            25,
            $ownership->acquisition_value
        );

        $this->assertSame(
            75,
            $participant
                ->team
                ->creditAccount
                ->fresh()
                ->current_balance
        );
    }

    public function test_it_rolls_back_nomination_closure_when_player_acquisition_fails(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'status' => AuctionNominationStatus::ACTIVE,
            'close_reason' => null,
            'closed_at' => null,
            'expires_at' => now()->subSecond(),
        ]);

        LeagueSeasonRosterRule::factory()->create([
            'league_season_id' => $auction
                ->marketSession
                ->league_season_id,
            'role' => $nomination->playerSeason->role,
            'max_players' => 3,
        ]);

        $participant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
        ]);

        $creditAccount = TeamCreditAccount::factory()->create([
            'team_id' => $participant->team_id,
            'current_balance' => 5,
        ]);

        AuctionBid::factory()->create([
            'auction_nomination_id' => $nomination->id,
            'auction_participant_id' => $participant->id,
            'amount' => 10,
            'sequence_number' => 1,
            'placed_at' => now(),
        ]);

        try {
            app(FinalizeAuctionNomination::class)
                ->execute(
                    $nomination,
                    AuctionNominationCloseReason::TIMER_EXPIRED
                );

            $this->fail('Expected RuntimeException was not thrown.');
        } catch (RuntimeException $exception) {
            $this->assertSame(
                'Winning participant has insufficient credits.',
                $exception->getMessage()
            );
        }

        $this->assertSame(
            AuctionNominationStatus::ACTIVE,
            $nomination->fresh()->status
        );

        $this->assertNull(
            $nomination->fresh()->close_reason
        );

        $this->assertNull(
            $nomination->fresh()->closed_at
        );

        $this->assertSame(
            5,
            $creditAccount->fresh()->current_balance
        );

        $this->assertDatabaseMissing('roster_ownerships', [
            'league_season_id' => $auction
                ->marketSession
                ->league_season_id,
            'player_season_id' => $nomination->player_season_id,
        ]);
    }
}
