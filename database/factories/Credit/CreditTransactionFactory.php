<?php

namespace Database\Factories\Credit;

use App\Domain\Credit\Enums\CreditTransactionType;
use App\Models\Credit\CreditTransaction;
use App\Models\Credit\TeamCreditAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CreditTransaction>
 */
class CreditTransactionFactory extends Factory
{
    protected $model = CreditTransaction::class;

    public function definition(): array
    {
        $balanceBefore = fake()->numberBetween(100, 1000);
        $amount = -fake()->numberBetween(1, $balanceBefore);

        return [
            'team_credit_account_id' => TeamCreditAccount::factory(),
            'type' => CreditTransactionType::PLAYER_ACQUISITION,
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceBefore + $amount,
            'description' => fake()->optional()->sentence(),
        ];
    }
}
