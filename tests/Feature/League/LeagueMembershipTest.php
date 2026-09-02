<?php

namespace Tests\Feature\League;

use App\Domain\League\Enums\LeagueMembershipRole;
use App\Domain\League\Enums\LeagueMembershipStatus;
use App\Models\League\League;
use App\Models\League\LeagueMembership;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class LeagueMembershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_league_membership_can_be_created_from_factory(): void
    {
        $membership = LeagueMembership::factory()->create();

        $this->assertDatabaseHas('league_memberships', [
            'id' => $membership->id,
            'league_id' => $membership->league_id,
            'user_id' => $membership->user_id,
        ]);
    }

    public function test_ulid_is_generated_automatically(): void
    {
        $membership = LeagueMembership::factory()->create();

        $this->assertNotNull($membership->ulid);
        $this->assertSame(26, strlen($membership->ulid));
    }

    public function test_role_is_cast_to_enum(): void
    {
        $membership = LeagueMembership::factory()->create();

        $this->assertSame(
            LeagueMembershipRole::MEMBER,
            $membership->role
        );
    }

    public function test_status_is_cast_to_enum(): void
    {
        $membership = LeagueMembership::factory()->create();

        $this->assertSame(
            LeagueMembershipStatus::ACTIVE,
            $membership->status
        );
    }

    public function test_joined_at_is_cast_to_datetime(): void
    {
        $membership = LeagueMembership::factory()->create();

        $this->assertInstanceOf(
            Carbon::class,
            $membership->joined_at
        );
    }

    public function test_president_factory_state_sets_president_role(): void
    {
        $membership = LeagueMembership::factory()
            ->president()
            ->create();

        $this->assertSame(
            LeagueMembershipRole::PRESIDENT,
            $membership->role
        );
    }

    public function test_inactive_factory_state_sets_inactive_status(): void
    {
        $membership = LeagueMembership::factory()
            ->inactive()
            ->create();

        $this->assertSame(
            LeagueMembershipStatus::INACTIVE,
            $membership->status
        );
    }

    public function test_same_user_cannot_have_duplicate_membership_in_same_league(): void
    {
        $league = League::factory()->create();
        $user = User::factory()->create();

        LeagueMembership::factory()->create([
            'league_id' => $league->id,
            'user_id' => $user->id,
        ]);

        $this->expectException(QueryException::class);

        LeagueMembership::factory()->create([
            'league_id' => $league->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_membership_belongs_to_league(): void
    {
        $membership = LeagueMembership::factory()->create();

        $this->assertInstanceOf(
            League::class,
            $membership->league
        );
    }

    public function test_membership_belongs_to_user(): void
    {
        $membership = LeagueMembership::factory()->create();

        $this->assertInstanceOf(
            User::class,
            $membership->user
        );
    }

    public function test_league_and_user_have_memberships(): void
    {
        $league = League::factory()->create();
        $user = User::factory()->create();

        $membership = LeagueMembership::factory()->create([
            'league_id' => $league->id,
            'user_id' => $user->id,
        ]);

        $this->assertTrue(
            $league->memberships->contains($membership)
        );

        $this->assertTrue(
            $user->leagueMemberships->contains($membership)
        );
    }
}
