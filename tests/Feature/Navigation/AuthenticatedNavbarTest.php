<?php

namespace Tests\Feature\Navigation;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticatedNavbarTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_authenticated_dashboard(): void
    {
        $response = $this->get(
            route('dashboard')
        );

        $response->assertRedirect(
            route('login')
        );
    }

    public function test_authenticated_user_can_see_authenticated_navbar(): void
    {
        $user = User::factory()->create([
            'name' => 'Andrea',
            'username' => 'andrea',
            'email_verified_at' => now(),
        ]);

        $response = $this
            ->actingAs($user)
            ->get(
                route('dashboard')
            );

        $response->assertOk();

        $response->assertSee(
            'Andrea'
        );

        $response->assertSee(
            '@andrea'
        );

        $response->assertSee(
            __('landing.nav.dashboard')
        );

        $response->assertSee(
            __('auth.login.logout')
        );
    }

    public function test_authenticated_navbar_contains_locale_and_theme_controls(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this
            ->actingAs($user)
            ->get(
                route('dashboard')
            );

        $response->assertOk();

        $response->assertSee(
            'data-theme-toggle',
            false
        );

        $response->assertSee(
            route(
                'locale.update',
                'it'
            ),
            false
        );

        $response->assertSee(
            route(
                'locale.update',
                'en'
            ),
            false
        );
    }

    public function test_authenticated_navbar_contains_logout_form(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this
            ->actingAs($user)
            ->get(
                route('dashboard')
            );

        $response->assertOk();

        $response->assertSee(
            route('logout'),
            false
        );

        $response->assertSee(
            __('auth.login.logout')
        );
    }
}
