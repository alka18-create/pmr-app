<?php

namespace Database\Factories;

use App\Enums\AchievementCategory;
use App\Enums\AchievementLevel;
use App\Models\Achievement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Achievement>
 */
class AchievementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->unique()->sentence(3),
            'level' => fake()->randomElement(AchievementLevel::cases()),
            'category' => fake()->randomElement(AchievementCategory::cases()),
            'year' => fake()->numberBetween(2020, (int) now()->year),
            'description' => fake()->paragraph(),
            'attachment_path' => null,
            'winner' => fake()->name(),
            'is_public' => true,
        ];
    }

    public function privat(): static
    {
        return $this->state(fn () => ['is_public' => false]);
    }
}
