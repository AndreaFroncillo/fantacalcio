<?php

namespace Database\Factories\Football;

use App\Domain\Football\Enums\RealClubStatus;
use App\Models\Football\RealClub;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<RealClub>
 */
class RealClubFactory extends Factory
{
    protected $model = RealClub::class;

    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'short_name' => strtoupper(fake()->unique()->lexify('???')),
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'logo_path' => null,
            'status' => RealClubStatus::ACTIVE,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RealClubStatus::INACTIVE,
        ]);
    }
}
