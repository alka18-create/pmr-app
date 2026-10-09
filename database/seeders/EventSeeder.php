<?php

namespace Database\Seeders;

use App\Enums\EventStatus;
use App\Enums\EventType;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        if (Event::query()->exists()) {
            return;
        }

        $user = User::query()->where('email', 'like', '%@pmr.test')->first()
            ?? User::factory()->create(['role' => 'pengurus']);

        Event::create([
            'title' => 'Latihan P3K Bulanan',
            'type' => EventType::LatihanRutin,
            'description' => 'Latihan pertolongan pertama rutin anggota PMR.',
            'location' => 'Aula Sekolah',
            'starts_at' => now()->addWeek()->startOfDay()->setTime(9, 0),
            'ends_at' => now()->addWeek()->startOfDay()->setTime(12, 0),
            'status' => EventStatus::Published,
            'is_public' => true,
            'created_by' => $user->id,
        ]);

        Event::create([
            'title' => 'Donor Darah bersama PMI',
            'type' => EventType::DonorDarah,
            'description' => 'Kegiatan donor darah bersama PMI kota.',
            'location' => 'Lapangan Utama',
            'starts_at' => now()->addMonth()->setTime(8, 0),
            'ends_at' => now()->addMonth()->setTime(14, 0),
            'status' => EventStatus::Draft,
            'is_public' => false,
            'created_by' => $user->id,
        ]);
    }
}
