<?php

namespace App\Support;

use Illuminate\Support\Str;

class PersonNameNormalizer
{
    public static function normalize(string $value): string
    {
        $normalized = Str::of($value)
            ->squish()
            ->lower()
            ->title()
            ->toString();

        return preg_replace_callback(
            "/(['’])(\p{L})/u",
            static fn (array $matches): string => $matches[1]
                .mb_strtoupper($matches[2]),
            $normalized
        ) ?? $normalized;
    }
}
