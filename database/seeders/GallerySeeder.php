<?php

namespace Database\Seeders;

use App\Models\GalleryCategory;
use App\Models\GalleryItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class GallerySeeder extends Seeder
{
    public function run(): void
    {
        if (GalleryCategory::query()->exists()) {
            return;
        }

        $user = User::query()->where('email', 'like', '%@pmr.test')->first()
            ?? User::factory()->create(['role' => 'pengurus']);

        $latihan = GalleryCategory::create([
            'name' => 'Latihan Rutin',
            'description' => 'Dokumentasi latihan rutin anggota.',
        ]);

        GalleryCategory::create([
            'name' => 'Bakti Sosial',
            'description' => 'Kegiatan bakti sosial dan donor darah.',
        ]);

        GalleryItem::create([
            'category_id' => $latihan->id,
            'title' => 'Latihan P3K di Aula',
            'description' => 'Suasana latihan P3K bulanan.',
            'file_path' => 'gallery/seed-latihan.jpg',
            'file_type' => 'image',
            'is_public' => true,
            'uploaded_by' => $user->id,
            'taken_at' => now()->subWeek()->toDateString(),
        ]);

        GalleryItem::create([
            'category_id' => $latihan->id,
            'title' => 'Video Tips P3K',
            'description' => 'Video YouTube tips P3K.',
            'file_path' => null,
            'file_type' => 'video',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'is_public' => true,
            'uploaded_by' => $user->id,
        ]);
    }
}
