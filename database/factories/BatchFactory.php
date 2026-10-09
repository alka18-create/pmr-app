<?php

namespace Database\Factories;

use App\Models\Batch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Batch>
 */
class BatchFactory extends Factory
{
    public function definition(): array
    {
        $year = fake()->unique()->numberBetween(2020, 2030);

        return [
            'name' => 'Angkatan '.$year.'-'.fake()->unique()->numberBetween(1, 999),
            'year' => $year,
        ];
    }
}
