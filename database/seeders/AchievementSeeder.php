<?php

namespace Database\Seeders;

use App\Models\Achievement;
use Illuminate\Database\Seeder;

class AchievementSeeder extends Seeder
{
    public function run(): void
    {
        if (Achievement::query()->exists()) {
            return;
        }

        Achievement::create([
            'title' => 'Juara 1 Lomba P3K Tingkat Kota',
            'level' => 'kota',
            'category' => 'lomba',
            'year' => (int) now()->year - 1,
            'description' => 'Tim PMR Wira meraih juara pertama lomba pertolongan pertama tingkat kota.',
            'winner' => 'Tim P3K PMR Wira',
            'is_public' => true,
        ]);

        Achievement::create([
            'title' => 'Penghargaan Organisasi Teladan Sekolah',
            'level' => 'sekolah',
            'category' => 'penghargaan',
            'year' => (int) now()->year,
            'description' => 'Penghargaan dari sekolah untuk kontribusi organisasi kesiswaan.',
            'winner' => null,
            'is_public' => true,
        ]);

        Achievement::create([
            'title' => 'Prestasi Internal Belum Publik',
            'level' => 'provinsi',
            'category' => 'lomba',
            'year' => (int) now()->year,
            'description' => 'Prestasi yang belum boleh tampil di publik.',
            'is_public' => false,
        ]);
    }
}
