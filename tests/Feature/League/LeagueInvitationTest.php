<?php

namespace Tests\Feature\League;

use App\Domain\League\Enums\LeagueInvitationStatus;
use App\Models\League\League;
use App\Models\League\LeagueInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class LeagueInvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_league_invitation_can_be_created_from_factory(): void
    {
        $invitation = LeagueInvitation::factory()->create();

        $this->assertDatabaseHas('league_invitations', [
            'id' => $invitation->id,
            'league_id' => $invitation->league_id,
            'invited_by_user_id' => $invitation->invited_by_user_id,
            'email' => $invitation->email,
        ]);
    }

    public function test_ulid_is_generated_automatically(): void
    {
        $invitation = LeagueInvitation::factory()->create();

        $this->assertNotNull($invitation->ulid);
        $this->assertSame(26, strlen($invitation->ulid));
    }

    public function test_token_is_generated_automatically(): void
    {
        $invitation = LeagueInvitation::factory()->create();

        $this->assertNotNull($invitation->token);
        $this->assertSame(64, strlen($invitation->token));
    }

    public function test_tokens_are_unique(): void
    {
        $firstInvitation = LeagueInvitation::factory()->create();
        $secondInvitation = LeagueInvitation::factory()->create();

        $this->assertNotSame(
            $firstInvitation->token,
            $secondInvitation->token
        );
    }

    public function test_status_is_cast_to_enum(): void
    {
        $invitation = LeagueInvitation::factory()->create();

        $this->assertSame(
            LeagueInvitationStatus::PENDING,
            $invitation->status
        );
    }

    public function test_dates_are_cast_to_datetime(): void
    {
        $invitation = LeagueInvitation::factory()->accepted()->create();

        $this->assertInstanceOf(Carbon::class, $invitation->expires_at);
        $this->assertInstanceOf(Carbon::class, $invitation->accepted_at);
    }

    public function test_pending_invitation_with_future_expiration_is_not_expired(): void
    {
        $invitation = LeagueInvitation::factory()->create();

        $this->assertFalse($invitation->isExpired());
    }

    public function test_pending_invitation_with_past_expiration_is_expired(): void
    {
        $invitation = LeagueInvitation::factory()->expired()->create();

        $this->assertTrue($invitation->isExpired());
    }

    public function test_accepted_invitation_is_not_considered_expired(): void
    {
        $invitation = LeagueInvitation::factory()
            ->accepted()
            ->create([
                'expires_at' => now()->subDay(),
            ]);

        $this->assertFalse($invitation->isExpired());
    }

    public function test_revoked_invitation_is_not_considered_expired(): void
    {
        $invitation = LeagueInvitation::factory()
            ->revoked()
            ->create([
                'expires_at' => now()->subDay(),
            ]);

        $this->assertFalse($invitation->isExpired());
    }

    public function test_invitation_belongs_to_league(): void
    {
        $invitation = LeagueInvitation::factory()->create();

        $this->assertInstanceOf(
            League::class,
            $invitation->league
        );
    }

    public function test_invitation_belongs_to_inviting_user(): void
    {
        $invitation = LeagueInvitation::factory()->create();

        $this->assertInstanceOf(
            User::class,
            $invitation->invitedBy
        );
    }

    public function test_accepted_factory_state_sets_accepted_status_and_date(): void
    {
        $invitation = LeagueInvitation::factory()
            ->accepted()
            ->create();

        $this->assertSame(
            LeagueInvitationStatus::ACCEPTED,
            $invitation->status
        );

        $this->assertNotNull($invitation->accepted_at);
    }

    public function test_revoked_factory_state_sets_revoked_status_and_date(): void
    {
        $invitation = LeagueInvitation::factory()
            ->revoked()
            ->create();

        $this->assertSame(
            LeagueInvitationStatus::REVOKED,
            $invitation->status
        );

        $this->assertNotNull($invitation->revoked_at);
    }

    public function test_league_has_invitations(): void
    {
        $league = League::factory()->create();

        $invitation = LeagueInvitation::factory()->create([
            'league_id' => $league->id,
        ]);

        $this->assertTrue(
            $league->invitations->contains($invitation)
        );
    }

    public function test_user_has_sent_league_invitations(): void
    {
        $user = User::factory()->create();

        $invitation = LeagueInvitation::factory()->create([
            'invited_by_user_id' => $user->id,
        ]);

        $this->assertTrue(
            $user->sentLeagueInvitations->contains($invitation)
        );
    }
}
