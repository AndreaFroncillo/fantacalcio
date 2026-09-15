<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_page_can_be_rendered(): void
    {
        $response = $this->get(
            route('register')
        );

        $response->assertOk();
    }

    public function test_new_user_can_register(): void
    {
        $response = $this->post(
            route('register.store'),
            [
                'name' => 'Andrea',
                'surname' => 'Froncillo',
                'username' => 'andrea',
                'email' => 'andrea@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ]
        );

        $user = User::query()
            ->where('email', 'andrea@example.com')
            ->first();

        $this->assertNotNull($user);

        $this->assertSame(
            'Andrea',
            $user->name
        );

        $this->assertSame(
            'Froncillo',
            $user->surname
        );

        $this->assertSame(
            'andrea',
            $user->username
        );

        $this->assertTrue(
            Hash::check(
                'password',
                $user->password
            )
        );

        $this->assertAuthenticatedAs($user);
    }

    public function test_username_and_email_are_normalized_to_lowercase(): void
    {
        $this->post(
            route('register.store'),
            [
                'name' => 'Andrea',
                'surname' => 'Froncillo',
                'username' => 'Andrea_10',
                'email' => 'ANDREA@EXAMPLE.COM',
                'password' => 'password',
                'password_confirmation' => 'password',
            ]
        );

        $this->assertDatabaseHas('users', [
            'username' => 'andrea_10',
            'email' => 'andrea@example.com',
        ]);
    }

    public function test_registration_requires_name(): void
    {
        $response = $this->post(
            route('register.store'),
            [
                'surname' => 'Froncillo',
                'username' => 'andrea',
                'email' => 'andrea@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ]
        );

        $response->assertSessionHasErrors('name');
    }

    public function test_registration_requires_surname(): void
    {
        $response = $this->post(
            route('register.store'),
            [
                'name' => 'Andrea',
                'username' => 'andrea',
                'email' => 'andrea@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ]
        );

        $response->assertSessionHasErrors('surname');
    }

    public function test_registration_requires_username(): void
    {
        $response = $this->post(
            route('register.store'),
            [
                'name' => 'Andrea',
                'surname' => 'Froncillo',
                'email' => 'andrea@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ]
        );

        $response->assertSessionHasErrors('username');
    }

    public function test_username_must_be_unique(): void
    {
        User::factory()->create([
            'username' => 'andrea',
        ]);

        $response = $this->post(
            route('register.store'),
            [
                'name' => 'Andrea',
                'surname' => 'Froncillo',
                'username' => 'ANDREA',
                'email' => 'andrea@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ]
        );

        $response->assertSessionHasErrors('username');
    }

    public function test_username_must_have_valid_format(): void
    {
        $response = $this->post(
            route('register.store'),
            [
                'name' => 'Andrea',
                'surname' => 'Froncillo',
                'username' => 'andrea froncillo!',
                'email' => 'andrea@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ]
        );

        $response->assertSessionHasErrors('username');
    }

    public function test_email_must_be_unique(): void
    {
        User::factory()->create([
            'email' => 'andrea@example.com',
        ]);

        $response = $this->post(
            route('register.store'),
            [
                'name' => 'Andrea',
                'surname' => 'Froncillo',
                'username' => 'andrea',
                'email' => 'ANDREA@EXAMPLE.COM',
                'password' => 'password',
                'password_confirmation' => 'password',
            ]
        );

        $response->assertSessionHasErrors('email');
    }

    public function test_password_must_be_confirmed(): void
    {
        $response = $this->post(
            route('register.store'),
            [
                'name' => 'Andrea',
                'surname' => 'Froncillo',
                'username' => 'andrea',
                'email' => 'andrea@example.com',
                'password' => 'password',
                'password_confirmation' => 'different-password',
            ]
        );

        $response->assertSessionHasErrors('password');
    }

    public function test_name_and_surname_are_normalized(): void
    {
        $this->post(
            route('register.store'),
            [
                'name' => '  aNDREA   mARIO  ',
                'surname' => '  fRONCILLO  ',
                'username' => 'andrea',
                'email' => 'andrea@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ]
        );

        $this->assertDatabaseHas('users', [
            'name' => 'Andrea Mario',
            'surname' => 'Froncillo',
            'username' => 'andrea',
            'email' => 'andrea@example.com',
        ]);
    }

    public function test_registration_uses_custom_validation_messages(): void
    {
        $this->app->setLocale('it');

        User::factory()->create([
            'username' => 'andrea',
            'email' => 'andrea@example.com',
        ]);

        $response = $this->post(
            route('register.store'),
            [
                'name' => 'Andrea',
                'surname' => 'Froncillo',
                'username' => 'ANDREA',
                'email' => 'ANDREA@EXAMPLE.COM',
                'password' => 'password',
                'password_confirmation' => 'different-password',
            ]
        );

        $response->assertSessionHasErrors([
            'username' => 'Questo username è già in uso.',
            'email' => 'Questa email è già associata a un account.',
            'password' => 'Le password non corrispondono.',
        ]);
    }
}
