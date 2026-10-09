<?php

namespace Database\Factories;

use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SchoolClass>
 */
class SchoolClassFactory extends Factory
{
    public function definition(): array
    {
        $level = fake()->randomElement(['X', 'XI', 'XII']);
        $suffix = fake()->unique()->numberBetween(1, 20);

        return [
            'name' => $level.' '.fake()->randomElement(['IPA', 'IPS']).' '.$suffix,
            'level' => $level,
            'year' => fake()->numberBetween(2020, 2030),
        ];
    }
}
