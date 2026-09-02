<?php

namespace Database\Factories\Credit;

use App\Models\Credit\TeamCreditAccount;
use App\Models\Team\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeamCreditAccount>
 */
class TeamCreditAccountFactory extends Factory
{
    protected $model = TeamCreditAccount::class;

    public function definition(): array
    {
        $initialBalance = fake()->numberBetween(100, 1000);

        return [
            'team_id' => Team::factory(),
            'initial_balance' => $initialBalance,
            'current_balance' => $initialBalance,
        ];
    }
}
