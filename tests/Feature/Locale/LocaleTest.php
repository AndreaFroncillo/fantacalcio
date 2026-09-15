<?php

namespace Tests\Feature\Locale;

use Tests\TestCase;

class LocaleTest extends TestCase
{
    public function test_it_stores_supported_locale_in_session(): void
    {
        $response = $this
            ->from('/')
            ->post(
                route(
                    'locale.update',
                    'en'
                )
            );

        $response->assertRedirect('/');

        $response->assertSessionHas(
            'locale',
            'en'
        );
    }

    public function test_locale_can_be_changed_to_italian(): void
    {
        $response = $this->post(
            route(
                'locale.update',
                'it'
            )
        );

        $response->assertSessionHas(
            'locale',
            'it'
        );

        $response->assertCookie(
            'locale',
            'it'
        );
    }

    public function test_locale_can_be_changed_to_english(): void
    {
        $response = $this->post(
            route(
                'locale.update',
                'en'
            )
        );

        $response->assertSessionHas(
            'locale',
            'en'
        );

        $response->assertCookie(
            'locale',
            'en'
        );
    }

    public function test_locale_is_restored_from_cookie(): void
    {
        $response = $this
            ->withCookie(
                'locale',
                'it'
            )
            ->get(
                route('login')
            );

        $response->assertOk();

        $response->assertSessionHas(
            'locale',
            'it'
        );

        $this->assertSame(
            'it',
            app()->getLocale()
        );
    }

    public function test_unsupported_locale_is_rejected(): void
    {
        $response = $this->post(
            route(
                'locale.update',
                'fr'
            )
        );

        $response->assertNotFound();

        $this->assertNotSame(
            'fr',
            session('locale')
        );
    }
}
