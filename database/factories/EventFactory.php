<?php

namespace Database\Factories;

use App\Enums\EventStatus;
use App\Enums\EventType;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);
        $startsAt = fake()->dateTimeBetween('+1 day', '+30 days');

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 99999),
            'type' => fake()->randomElement(EventType::cases()),
            'description' => fake()->paragraph(),
            'location' => fake()->streetAddress(),
            'starts_at' => $startsAt,
            'ends_at' => (clone $startsAt)->modify('+'.fake()->numberBetween(1, 4).' hours'),
            'cover_path' => null,
            'status' => EventStatus::Draft,
            'is_public' => true,
            'max_participants' => fake()->numberBetween(20, 100),
            'created_by' => User::factory(),
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => ['status' => EventStatus::Published]);
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => EventStatus::Draft]);
    }

    public function selesai(): static
    {
        return $this->state(fn () => ['status' => EventStatus::Selesai]);
    }

    public function batal(): static
    {
        return $this->state(fn () => ['status' => EventStatus::Batal]);
    }

    public function privat(): static
    {
        return $this->state(fn () => ['is_public' => false]);
    }
}
