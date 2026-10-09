<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventDocument>
 */
class EventDocumentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'file_path' => 'events/'.fake()->numberBetween(1, 99).'/'.fake()->uuid().'.jpg',
            'file_type' => 'image',
            'caption' => fake()->sentence(),
            'is_public' => false,
            'uploaded_by' => User::factory(),
        ];
    }
}
