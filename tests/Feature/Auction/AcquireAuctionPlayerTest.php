<?php

namespace Tests\Feature\Auction;

use App\Domain\Auction\Actions\AcquireAuctionPlayer;
use App\Domain\Auction\Enums\AuctionNominationCloseReason;
use App\Domain\Auction\Enums\AuctionNominationStatus;
use App\Domain\Credit\Enums\CreditTransactionType;
use App\Models\Auction\Auction;
use App\Models\Auction\AuctionBid;
use App\Models\Auction\AuctionNomination;
use App\Models\Auction\AuctionParticipant;
use App\Models\Credit\TeamCreditAccount;
use App\Models\Football\PlayerSeason;
use App\Models\Roster\LeagueSeasonRosterRule;
use App\Models\Roster\RosterOwnership;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class AcquireAuctionPlayerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_acquires_player_for_winning_participant(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'status' => AuctionNominationStatus::COMPLETED,
            'close_reason' => AuctionNominationCloseReason::TIMER_EXPIRED,
            'closed_at' => now(),
        ]);

        $this->createRosterRuleForNomination($nomination);

        $participant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
        ]);

        TeamCreditAccount::factory()->create([
            'team_id' => $participant->team_id,
        ]);

        AuctionBid::factory()->create([
            'auction_nomination_id' => $nomination->id,
            'auction_participant_id' => $participant->id,
            'amount' => 10,
            'sequence_number' => 1,
            'placed_at' => now(),
        ]);

        $ownership = app(AcquireAuctionPlayer::class)
            ->execute($nomination);

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

        $this->assertNull($ownership->released_at);
    }

    public function test_it_fails_when_nomination_is_not_completed(): void
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

        TeamCreditAccount::factory()->create([
            'team_id' => $participant->team_id,
        ]);

        AuctionBid::factory()->create([
            'auction_nomination_id' => $nomination->id,
            'auction_participant_id' => $participant->id,
            'amount' => 10,
            'sequence_number' => 1,
            'placed_at' => now(),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Auction nomination is not completed.'
        );

        app(AcquireAuctionPlayer::class)
            ->execute($nomination);
    }

    public function test_it_fails_when_nomination_was_rejected_by_president(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'status' => AuctionNominationStatus::REJECTED,
            'close_reason' => AuctionNominationCloseReason::PRESIDENT_REJECTED,
            'closed_at' => now(),
        ]);

        $participant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
        ]);

        TeamCreditAccount::factory()->create([
            'team_id' => $participant->team_id,
        ]);

        AuctionBid::factory()->create([
            'auction_nomination_id' => $nomination->id,
            'auction_participant_id' => $participant->id,
            'amount' => 10,
            'sequence_number' => 1,
            'placed_at' => now(),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Auction nomination is not completed.'
        );

        app(AcquireAuctionPlayer::class)
            ->execute($nomination);
    }

    public function test_it_returns_null_when_completed_nomination_has_no_bids(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'status' => AuctionNominationStatus::COMPLETED,
            'close_reason' => AuctionNominationCloseReason::TIMER_EXPIRED,
            'closed_at' => now(),
        ]);

        $ownership = app(AcquireAuctionPlayer::class)
            ->execute($nomination);

        $this->assertNull($ownership);

        $this->assertDatabaseMissing('roster_ownerships', [
            'league_season_id' => $auction->marketSession->league_season_id,
            'player_season_id' => $nomination->player_season_id,
        ]);
    }

    public function test_it_acquires_player_for_participant_with_highest_bid_sequence(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'status' => AuctionNominationStatus::COMPLETED,
            'close_reason' => AuctionNominationCloseReason::TIMER_EXPIRED,
            'closed_at' => now(),
        ]);

        $this->createRosterRuleForNomination($nomination);

        $firstParticipant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'nomination_position' => 1,
        ]);

        $secondParticipant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
            'nomination_position' => 2,
        ]);

        TeamCreditAccount::factory()->create([
            'team_id' => $firstParticipant->team_id,
        ]);

        TeamCreditAccount::factory()->create([
            'team_id' => $secondParticipant->team_id,
        ]);

        AuctionBid::factory()->create([
            'auction_nomination_id' => $nomination->id,
            'auction_participant_id' => $secondParticipant->id,
            'amount' => 20,
            'sequence_number' => 2,
            'placed_at' => now(),
        ]);

        AuctionBid::factory()->create([
            'auction_nomination_id' => $nomination->id,
            'auction_participant_id' => $firstParticipant->id,
            'amount' => 10,
            'sequence_number' => 1,
            'placed_at' => now()->addSecond(),
        ]);

        $ownership = app(AcquireAuctionPlayer::class)
            ->execute($nomination);

        $this->assertSame(
            $secondParticipant->team_id,
            $ownership->team_id
        );

        $this->assertSame(
            20,
            $ownership->acquisition_value
        );
    }

    public function test_it_fails_when_winner_no_longer_has_enough_credits(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'status' => AuctionNominationStatus::COMPLETED,
            'close_reason' => AuctionNominationCloseReason::TIMER_EXPIRED,
            'closed_at' => now(),
        ]);

        $this->createRosterRuleForNomination($nomination);

        $participant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
        ]);

        TeamCreditAccount::factory()->create([
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

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Winning participant has insufficient credits.'
        );

        app(AcquireAuctionPlayer::class)
            ->execute($nomination);
    }

    public function test_it_deducts_winning_bid_from_credit_balance(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'status' => AuctionNominationStatus::COMPLETED,
            'close_reason' => AuctionNominationCloseReason::TIMER_EXPIRED,
            'closed_at' => now(),
        ]);

        $this->createRosterRuleForNomination($nomination);

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

        app(AcquireAuctionPlayer::class)
            ->execute($nomination);

        $this->assertSame(
            90,
            $creditAccount->fresh()->current_balance
        );
    }

    public function test_it_creates_credit_transaction_for_player_acquisition(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'status' => AuctionNominationStatus::COMPLETED,
            'close_reason' => AuctionNominationCloseReason::TIMER_EXPIRED,
            'closed_at' => now(),
        ]);

        $this->createRosterRuleForNomination($nomination);

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

        app(AcquireAuctionPlayer::class)
            ->execute($nomination);

        $this->assertDatabaseHas('credit_transactions', [
            'team_credit_account_id' => $creditAccount->id,
            'type' => CreditTransactionType::PLAYER_ACQUISITION->value,
            'amount' => -10,
            'balance_before' => 100,
            'balance_after' => 90,
        ]);
    }

    public function test_it_fails_when_player_is_already_actively_owned_in_league_season(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'status' => AuctionNominationStatus::COMPLETED,
            'close_reason' => AuctionNominationCloseReason::TIMER_EXPIRED,
            'closed_at' => now(),
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

        RosterOwnership::factory()->create([
            'league_season_id' => $auction->marketSession->league_season_id,
            'player_season_id' => $nomination->player_season_id,
            'released_at' => null,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Player is already owned in this league season.'
        );

        app(AcquireAuctionPlayer::class)
            ->execute($nomination);
    }

    public function test_it_allows_acquisition_when_previous_ownership_was_released(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'status' => AuctionNominationStatus::COMPLETED,
            'close_reason' => AuctionNominationCloseReason::TIMER_EXPIRED,
            'closed_at' => now(),
        ]);

        $this->createRosterRuleForNomination($nomination);

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

        RosterOwnership::factory()
            ->released()
            ->create([
                'league_season_id' => $auction->marketSession->league_season_id,
                'player_season_id' => $nomination->player_season_id,
            ]);

        $ownership = app(AcquireAuctionPlayer::class)
            ->execute($nomination);

        $this->assertNotNull($ownership);

        $this->assertSame(
            $nomination->player_season_id,
            $ownership->player_season_id
        );

        $this->assertNull($ownership->released_at);
    }

    public function test_it_fails_when_winning_team_has_no_credit_account(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'status' => AuctionNominationStatus::COMPLETED,
            'close_reason' => AuctionNominationCloseReason::TIMER_EXPIRED,
            'closed_at' => now(),
        ]);

        $this->createRosterRuleForNomination($nomination);

        $participant = AuctionParticipant::factory()->create([
            'auction_id' => $auction->id,
        ]);

        AuctionBid::factory()->create([
            'auction_nomination_id' => $nomination->id,
            'auction_participant_id' => $participant->id,
            'amount' => 10,
            'sequence_number' => 1,
            'placed_at' => now(),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Auction participant team has no credit account.'
        );

        app(AcquireAuctionPlayer::class)
            ->execute($nomination);
    }

    public function test_it_fails_when_winning_participant_belongs_to_different_auction(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        $otherAuction = Auction::factory()
            ->live()
            ->create();

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'status' => AuctionNominationStatus::COMPLETED,
            'close_reason' => AuctionNominationCloseReason::TIMER_EXPIRED,
            'closed_at' => now(),
        ]);

        $participant = AuctionParticipant::factory()->create([
            'auction_id' => $otherAuction->id,
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

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Winning participant does not belong to the nomination auction.'
        );

        app(AcquireAuctionPlayer::class)
            ->execute($nomination);
    }

    public function test_it_fails_when_winning_team_has_completed_role_quota(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'status' => AuctionNominationStatus::COMPLETED,
            'close_reason' => AuctionNominationCloseReason::TIMER_EXPIRED,
            'closed_at' => now(),
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

        $leagueSeasonId = $auction
            ->marketSession
            ->league_season_id;

        $rosterRule = LeagueSeasonRosterRule::factory()->create([
            'league_season_id' => $leagueSeasonId,
            'role' => $nomination->playerSeason->role,
            'max_players' => 2,
        ]);

        for ($i = 0; $i < $rosterRule->max_players; $i++) {
            $playerSeason = PlayerSeason::factory()->create([
                'football_season_id' => $nomination
                    ->playerSeason
                    ->football_season_id,
                'role' => $nomination->playerSeason->role,
            ]);

            RosterOwnership::factory()->create([
                'league_season_id' => $leagueSeasonId,
                'team_id' => $participant->team_id,
                'player_season_id' => $playerSeason->id,
                'released_at' => null,
            ]);
        }

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Winning team has completed the role quota.'
        );

        app(AcquireAuctionPlayer::class)
            ->execute($nomination);
    }

    public function test_it_does_not_change_credits_when_role_quota_is_completed(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'status' => AuctionNominationStatus::COMPLETED,
            'close_reason' => AuctionNominationCloseReason::TIMER_EXPIRED,
            'closed_at' => now(),
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

        $leagueSeasonId = $auction
            ->marketSession
            ->league_season_id;

        $rosterRule = LeagueSeasonRosterRule::factory()->create([
            'league_season_id' => $leagueSeasonId,
            'role' => $nomination->playerSeason->role,
            'max_players' => 1,
        ]);

        $playerSeason = PlayerSeason::factory()->create([
            'football_season_id' => $nomination
                ->playerSeason
                ->football_season_id,
            'role' => $nomination->playerSeason->role,
        ]);

        RosterOwnership::factory()->create([
            'league_season_id' => $leagueSeasonId,
            'team_id' => $participant->team_id,
            'player_season_id' => $playerSeason->id,
            'released_at' => null,
        ]);

        try {
            app(AcquireAuctionPlayer::class)
                ->execute($nomination);

            $this->fail('Expected RuntimeException was not thrown.');
        } catch (RuntimeException $exception) {
            $this->assertSame(
                'Winning team has completed the role quota.',
                $exception->getMessage()
            );
        }

        $this->assertSame(
            100,
            $creditAccount->fresh()->current_balance
        );

        $this->assertDatabaseMissing('credit_transactions', [
            'team_credit_account_id' => $creditAccount->id,
            'type' => CreditTransactionType::PLAYER_ACQUISITION->value,
        ]);
    }

    public function test_it_rolls_back_credit_changes_when_ownership_creation_fails(): void
    {
        $auction = Auction::factory()
            ->live()
            ->create();

        $nomination = AuctionNomination::factory()->create([
            'auction_id' => $auction->id,
            'status' => AuctionNominationStatus::COMPLETED,
            'close_reason' => AuctionNominationCloseReason::TIMER_EXPIRED,
            'closed_at' => now(),
        ]);

        $this->createRosterRuleForNomination($nomination);

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

        RosterOwnership::creating(function () {
            throw new RuntimeException(
                'Simulated ownership creation failure.'
            );
        });

        try {
            app(AcquireAuctionPlayer::class)
                ->execute($nomination);

            $this->fail('Expected RuntimeException was not thrown.');
        } catch (RuntimeException $exception) {
            $this->assertSame(
                'Simulated ownership creation failure.',
                $exception->getMessage()
            );
        }

        $this->assertSame(
            100,
            $creditAccount->fresh()->current_balance
        );

        $this->assertDatabaseMissing('credit_transactions', [
            'team_credit_account_id' => $creditAccount->id,
            'type' => CreditTransactionType::PLAYER_ACQUISITION->value,
        ]);

        $this->assertDatabaseMissing('roster_ownerships', [
            'league_season_id' => $auction
                ->marketSession
                ->league_season_id,
            'team_id' => $participant->team_id,
            'player_season_id' => $nomination->player_season_id,
        ]);
    }

    private function createRosterRuleForNomination(
        AuctionNomination $nomination
    ): LeagueSeasonRosterRule {
        return LeagueSeasonRosterRule::factory()->create([
            'league_season_id' => $nomination
                ->auction
                ->marketSession
                ->league_season_id,
            'role' => $nomination->playerSeason->role,
            'max_players' => 8,
        ]);
    }
}
