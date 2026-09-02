<?php

namespace Tests\Feature\Credit;

use App\Models\Credit\TeamCreditAccount;
use App\Models\Team\Team;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamCreditAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_team_credit_account_can_be_created_from_factory(): void
    {
        $creditAccount = TeamCreditAccount::factory()->create();

        $this->assertDatabaseHas('team_credit_accounts', [
            'id' => $creditAccount->id,
            'team_id' => $creditAccount->team_id,
        ]);
    }

    public function test_ulid_is_generated_automatically(): void
    {
        $creditAccount = TeamCreditAccount::factory()->create();

        $this->assertNotNull($creditAccount->ulid);
        $this->assertSame(26, strlen($creditAccount->ulid));
    }

    public function test_balances_are_cast_to_integer(): void
    {
        $creditAccount = TeamCreditAccount::factory()->create([
            'initial_balance' => 500,
            'current_balance' => 450,
        ]);

        $this->assertSame(500, $creditAccount->initial_balance);
        $this->assertSame(450, $creditAccount->current_balance);
    }

    public function test_credit_account_belongs_to_team(): void
    {
        $team = Team::factory()->create();

        $creditAccount = TeamCreditAccount::factory()->create([
            'team_id' => $team->id,
        ]);

        $this->assertTrue(
            $creditAccount->team->is($team)
        );
    }

    public function test_team_has_credit_account(): void
    {
        $team = Team::factory()->create();

        $creditAccount = TeamCreditAccount::factory()->create([
            'team_id' => $team->id,
        ]);

        $this->assertTrue(
            $team->creditAccount->is($creditAccount)
        );
    }

    public function test_team_cannot_have_two_credit_accounts(): void
    {
        $team = Team::factory()->create();

        TeamCreditAccount::factory()->create([
            'team_id' => $team->id,
        ]);

        $this->expectException(QueryException::class);

        TeamCreditAccount::factory()->create([
            'team_id' => $team->id,
        ]);
    }
}
