<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_can_be_rendered(): void
    {
        $response = $this->get(
            route('login')
        );

        $response->assertOk();
    }

    public function test_user_can_login_with_email(): void
    {
        $user = User::factory()->create([
            'email' => 'andrea@example.com',
            'password' => 'password',
        ]);

        $response = $this->post(
            route('login.store'),
            [
                'email' => 'andrea@example.com',
                'password' => 'password',
            ]
        );

        $this->assertAuthenticatedAs($user);

        $response->assertRedirect(
            route('dashboard')
        );
    }

    public function test_user_can_login_with_username(): void
    {
        $user = User::factory()->create([
            'username' => 'andrea',
            'password' => 'password',
        ]);

        $response = $this->post(
            route('login.store'),
            [
                'email' => 'andrea',
                'password' => 'password',
            ]
        );

        $this->assertAuthenticatedAs($user);

        $response->assertRedirect(
            route('dashboard')
        );
    }

    public function test_email_login_is_case_insensitive(): void
    {
        $user = User::factory()->create([
            'email' => 'andrea@example.com',
            'password' => 'password',
        ]);

        $this->post(
            route('login.store'),
            [
                'email' => 'ANDREA@EXAMPLE.COM',
                'password' => 'password',
            ]
        );

        $this->assertAuthenticatedAs($user);
    }

    public function test_username_login_is_case_insensitive(): void
    {
        $user = User::factory()->create([
            'username' => 'andrea',
            'password' => 'password',
        ]);

        $this->post(
            route('login.store'),
            [
                'email' => 'ANDREA',
                'password' => 'password',
            ]
        );

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_wrong_password(): void
    {
        User::factory()->create([
            'username' => 'andrea',
            'password' => 'password',
        ]);

        $response = $this->post(
            route('login.store'),
            [
                'email' => 'andrea',
                'password' => 'wrong-password',
            ]
        );

        $this->assertGuest();

        $response->assertSessionHasErrors(
            'email'
        );
    }

    public function test_user_cannot_login_with_unknown_identifier(): void
    {
        $response = $this->post(
            route('login.store'),
            [
                'email' => 'unknown_user',
                'password' => 'password',
            ]
        );

        $this->assertGuest();

        $response->assertSessionHasErrors(
            'email'
        );
    }
}
