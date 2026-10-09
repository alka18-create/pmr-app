<?php

namespace Database\Factories;

use App\Enums\ProgramStatus;
use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Program>
 */
class ProgramFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->unique()->sentence(3),
            'year' => (int) now()->year,
            'description' => fake()->paragraph(),
            'phase' => fake()->randomElement(['Q1', 'Q2', 'Q3', 'Q4']),
            'status' => ProgramStatus::Rencana,
            'owner_id' => User::factory(),
        ];
    }
}
