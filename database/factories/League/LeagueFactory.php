<?php

namespace Database\Factories\League;

use App\Models\League\League;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<League>
 */
class LeagueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'description' => fake()->optional()->sentence(),
            'join_code' => strtoupper(
                fake()->unique()->bothify('??######')
            ),
            'timezone' => fake()->timezone(),
        ];
    }
}
