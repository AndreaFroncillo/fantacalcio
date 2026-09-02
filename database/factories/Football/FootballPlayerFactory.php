<?php

namespace Database\Factories\Football;

use App\Domain\Football\Enums\FootballPlayerStatus;
use App\Models\Football\FootballPlayer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FootballPlayer>
 */
class FootballPlayerFactory extends Factory
{
    protected $model = FootballPlayer::class;

    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'display_name' => "{$firstName} {$lastName}",
            'date_of_birth' => fake()->optional()->dateTimeBetween('-40 years', '-18 years'),
            'nationality' => fake()->optional()->country(),
            'photo_path' => null,
            'status' => FootballPlayerStatus::ACTIVE,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => FootballPlayerStatus::INACTIVE,
        ]);
    }
}
