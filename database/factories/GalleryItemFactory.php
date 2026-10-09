<?php

namespace Database\Factories;

use App\Enums\GalleryFileType;
use App\Models\Event;
use App\Models\GalleryCategory;
use App\Models\GalleryItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GalleryItem>
 */
class GalleryItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'category_id' => GalleryCategory::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->sentence(),
            'file_path' => 'gallery/'.fake()->uuid().'.jpg',
            'file_type' => GalleryFileType::Image,
            'video_url' => null,
            'event_id' => Event::factory(),
            'is_public' => true,
            'uploaded_by' => User::factory(),
            'taken_at' => fake()->date(),
        ];
    }

    public function video(): static
    {
        return $this->state(fn () => [
            'file_type' => GalleryFileType::Video,
            'file_path' => null,
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);
    }

    public function privat(): static
    {
        return $this->state(fn () => ['is_public' => false]);
    }
}
