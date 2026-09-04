<?php

namespace Tests\Feature\Credit;

use App\Domain\Credit\Enums\CreditTransactionType;
use App\Models\Credit\CreditTransaction;
use App\Models\Credit\TeamCreditAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreditTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_credit_transaction_can_be_created_from_factory(): void
    {
        $transaction = CreditTransaction::factory()->create();

        $this->assertDatabaseHas('credit_transactions', [
            'id' => $transaction->id,
            'team_credit_account_id' => $transaction->team_credit_account_id,
        ]);
    }

    public function test_ulid_is_generated_automatically(): void
    {
        $transaction = CreditTransaction::factory()->create();

        $this->assertNotNull($transaction->ulid);
        $this->assertSame(26, strlen($transaction->ulid));
    }

    public function test_type_is_cast_to_enum(): void
    {
        $transaction = CreditTransaction::factory()->create([
            'type' => CreditTransactionType::PLAYER_RELEASE,
        ]);

        $this->assertSame(
            CreditTransactionType::PLAYER_RELEASE,
            $transaction->type
        );
    }

    public function test_numeric_fields_are_cast_to_integer(): void
    {
        $transaction = CreditTransaction::factory()->create([
            'amount' => -40,
            'balance_before' => 500,
            'balance_after' => 460,
        ]);

        $this->assertSame(-40, $transaction->amount);
        $this->assertSame(500, $transaction->balance_before);
        $this->assertSame(460, $transaction->balance_after);
    }

    public function test_credit_transaction_belongs_to_team_credit_account(): void
    {
        $creditAccount = TeamCreditAccount::factory()->create();

        $transaction = CreditTransaction::factory()->create([
            'team_credit_account_id' => $creditAccount->id,
        ]);

        $this->assertTrue(
            $transaction->teamCreditAccount->is($creditAccount)
        );
    }

    public function test_team_credit_account_has_transactions(): void
    {
        $creditAccount = TeamCreditAccount::factory()->create();

        $transaction = CreditTransaction::factory()->create([
            'team_credit_account_id' => $creditAccount->id,
        ]);

        $this->assertTrue(
            $creditAccount->transactions->contains($transaction)
        );
    }

    public function test_transaction_balances_are_consistent_with_amount(): void
    {
        $transaction = CreditTransaction::factory()->create([
            'amount' => -40,
            'balance_before' => 500,
            'balance_after' => 460,
        ]);

        $this->assertSame(
            $transaction->balance_after,
            $transaction->balance_before + $transaction->amount
        );
    }

    public function test_description_can_be_null(): void
    {
        $transaction = CreditTransaction::factory()->create([
            'description' => null,
        ]);

        $this->assertNull($transaction->description);
    }
}
