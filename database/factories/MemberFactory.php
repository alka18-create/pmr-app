<?php

namespace Database\Factories;

use App\Enums\Gender;
use App\Enums\MemberStatus;
use App\Models\Batch;
use App\Models\Member;
use App\Models\Position;
use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Member>
 */
class MemberFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->name();

        return [
            'user_id' => null,
            'nis' => fake()->unique()->numerify('########'),
            'nisn' => fake()->unique()->numerify('##########'),
            'full_name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 99999),
            'gender' => fake()->randomElement([Gender::Laki, Gender::Perempuan]),
            'birth_date' => fake()->dateTimeBetween('-18 years', '-14 years')->format('Y-m-d'),
            'birth_place' => fake()->city(),
            'address' => fake()->address(),
            'phone' => fake()->numerify('08##########'),
            'email_pribadi' => fake()->unique()->safeEmail(),
            'parent_name' => fake()->name(),
            'parent_phone' => fake()->numerify('08##########'),
            'blood_type' => fake()->randomElement(['A', 'B', 'AB', 'O']),
            'batch_id' => Batch::factory(),
            'class_id' => SchoolClass::factory(),
            'position_id' => Position::factory(),
            'photo_path' => null,
            'status' => MemberStatus::Aktif,
            'joined_at' => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
            'notes' => null,
        ];
    }

    public function nonaktif(): static
    {
        return $this->state(fn () => ['status' => MemberStatus::Nonaktif]);
    }

    public function alumni(): static
    {
        return $this->state(fn () => ['status' => MemberStatus::Alumni]);
    }
}
