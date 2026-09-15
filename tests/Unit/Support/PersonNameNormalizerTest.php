<?php

namespace Tests\Unit\Support;

use App\Support\PersonNameNormalizer;
use PHPUnit\Framework\TestCase;

class PersonNameNormalizerTest extends TestCase
{
    public function test_it_normalizes_person_names(): void
    {
        $this->assertSame(
            'Andrea Mario',
            PersonNameNormalizer::normalize(
                '  aNDREA   mARIO  '
            )
        );

        $this->assertSame(
            'Maria Chiara',
            PersonNameNormalizer::normalize(
                'mARIA cHIARA'
            )
        );

        $this->assertSame(
            'De Luca',
            PersonNameNormalizer::normalize(
                'de lUCA'
            )
        );
    }

    public function test_it_capitalizes_letters_after_apostrophes(): void
    {
        $this->assertSame(
            "D'Amico",
            PersonNameNormalizer::normalize(
                "d'AMICO"
            )
        );

        $this->assertSame(
            'D’Amico',
            PersonNameNormalizer::normalize(
                'd’AMICO'
            )
        );
    }
}
