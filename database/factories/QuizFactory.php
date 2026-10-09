<?php

namespace Database\Factories;

use App\Models\Material;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Quiz>
 */
class QuizFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->sentence(2);

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 99999),
            'description' => fake()->sentence(),
            'material_id' => Material::factory(),
            'time_limit_minutes' => 30,
            'max_attempts' => 3,
            'pass_score' => 70,
            'is_active' => true,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
            'created_by' => User::factory(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function withoutLimit(): static
    {
        return $this->state(fn () => ['time_limit_minutes' => null]);
    }
}
