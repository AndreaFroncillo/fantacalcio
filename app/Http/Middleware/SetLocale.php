<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $locale = session(
            'locale',
            $request->cookie(
                'locale',
                config('app.locale', 'it')
            )
        );

        if (! in_array($locale, ['it', 'en'], true)) {
            $locale = 'it';
        }

        session()->put(
            'locale',
            $locale
        );

        App::setLocale(
            $locale
        );

        return $next($request);
    }
}
