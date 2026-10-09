<?php

namespace Database\Factories;

use App\Enums\MaterialCategory;
use App\Enums\MaterialLevel;
use App\Models\Material;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Material>
 */
class MaterialFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 99999),
            'category' => fake()->randomElement(MaterialCategory::cases()),
            'content' => fake()->paragraph(),
            'file_path' => null,
            'level' => MaterialLevel::Mula,
            'order' => fake()->numberBetween(1, 50),
            'is_public' => true,
            'uploaded_by' => User::factory(),
        ];
    }
}
