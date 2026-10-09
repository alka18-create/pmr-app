<?php

namespace Database\Factories;

use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Position>
 */
class PositionFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Ketua',
            'Wakil Ketua',
            'Sekretaris',
            'Bendahara',
            'Koordinator Bidang Kegiatan',
            'Anggota Biasa',
        ]).'-'.fake()->unique()->numberBetween(1, 999);

        return [
            'name' => $name,
            'description' => fake()->sentence(),
            'level' => fake()->numberBetween(1, 9),
        ];
    }
}
