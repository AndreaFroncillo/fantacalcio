<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_available_username_is_reported_as_available(): void
    {
        $response = $this->getJson(
            route('register.availability', [
                'field' => 'username',
                'value' => 'andrea',
            ])
        );

        $response
            ->assertOk()
            ->assertJson([
                'available' => true,
            ]);
    }

    public function test_existing_username_is_reported_as_unavailable(): void
    {
        User::factory()->create([
            'username' => 'mario_rossi',
        ]);

        $response = $this->getJson(
            route('register.availability', [
                'field' => 'username',
                'value' => 'MARIO_ROSSI',
            ])
        );

        $response
            ->assertOk()
            ->assertJson([
                'available' => false,
            ]);
    }

    public function test_available_email_is_reported_as_available(): void
    {
        $response = $this->getJson(
            route('register.availability', [
                'field' => 'email',
                'value' => 'andrea@example.com',
            ])
        );

        $response
            ->assertOk()
            ->assertJson([
                'available' => true,
            ]);
    }

    public function test_existing_email_is_reported_as_unavailable(): void
    {
        User::factory()->create([
            'email' => 'andrea@example.com',
        ]);

        $response = $this->getJson(
            route('register.availability', [
                'field' => 'email',
                'value' => 'ANDREA@EXAMPLE.COM',
            ])
        );

        $response
            ->assertOk()
            ->assertJson([
                'available' => false,
            ]);
    }

    public function test_field_must_be_username_or_email(): void
    {
        $response = $this->getJson(
            route('register.availability', [
                'field' => 'password',
                'value' => 'password',
            ])
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'field',
            ]);
    }

    public function test_value_is_required(): void
    {
        $response = $this->getJson(
            route('register.availability', [
                'field' => 'username',
            ])
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'value',
            ]);
    }
}
