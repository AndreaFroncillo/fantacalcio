<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_created_from_factory(): void
    {
        $user = User::factory()->create();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => $user->email,
            'username' => $user->username,
        ]);
    }

    public function test_ulid_is_generated_automatically(): void
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->ulid);
        $this->assertSame(26, strlen($user->ulid));
    }

    public function test_users_have_unique_ulids(): void
    {
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();

        $this->assertNotSame(
            $firstUser->ulid,
            $secondUser->ulid
        );
    }

    public function test_user_has_active_status_by_default(): void
    {
        $user = User::factory()
            ->state([
                'status' => null,
            ])
            ->make();

        unset($user->status);

        $user->save();
        $user->refresh();

        $this->assertSame('active', $user->status);
    }

    public function test_password_is_hashed(): void
    {
        $user = User::create([
            'name' => 'Andrea',
            'surname' => 'Froncillo',
            'username' => 'andrea',
            'email' => 'andrea@example.com',
            'password' => 'password',
        ]);

        $this->assertNotSame('password', $user->password);
        $this->assertTrue(
            Hash::check('password', $user->password)
        );
    }

    public function test_user_dates_are_cast_to_datetime(): void
    {
        $user = User::factory()->create([
            'last_login_at' => now(),
        ]);

        $this->assertInstanceOf(
            Carbon::class,
            $user->email_verified_at
        );

        $this->assertInstanceOf(
            Carbon::class,
            $user->last_login_at
        );
    }
}
