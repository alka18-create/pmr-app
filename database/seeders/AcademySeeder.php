<?php

namespace Database\Seeders;

use App\Models\Material;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Database\Seeder;

class AcademySeeder extends Seeder
{
    public function run(): void
    {
        if (Material::query()->exists()) {
            return;
        }

        $author = User::query()->where('role', 'pengurus')->first()
            ?? User::query()->where('role', 'admin')->first()
            ?? User::factory()->create(['role' => 'pengurus']);

        $material = Material::create([
            'title' => 'Dasar-Dasar P3K',
            'category' => 'dasar_p3k',
            'content' => 'Materi pengenalan pertolongan pertama pada kecelakaan.',
            'level' => 'mula',
            'order' => 1,
            'is_public' => true,
            'uploaded_by' => $author->id,
            'file_path' => null,
        ]);

        $quiz = Quiz::create([
            'title' => 'Quiz Dasar P3K',
            'description' => 'Uji pemahaman materi P3K tingkat Mula.',
            'material_id' => $material->id,
            'time_limit_minutes' => 15,
            'max_attempts' => 3,
            'pass_score' => 70,
            'is_active' => true,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
            'created_by' => $author->id,
        ]);

        $q1 = $quiz->questions()->create([
            'question' => 'Apa langkah pertama saat menolong korban kecelakaan?',
            'type' => 'multiple_choice',
            'score' => 50,
            'order' => 0,
        ]);

        foreach ([
            ['Pastikan keselamatan diri dan lingkungan', true],
            ['Langsung memindahkan korban', false],
            ['Memberi minum kepada korban', false],
            ['Merekam kejadian dengan ponsel', false],
        ] as $i => [$text, $correct]) {
            $q1->options()->create([
                'option_text' => $text,
                'is_correct' => $correct,
                'order' => $i,
            ]);
        }

        $q2 = $quiz->questions()->create([
            'question' => 'Nomor darurat medis di Indonesia adalah…',
            'type' => 'multiple_choice',
            'score' => 50,
            'order' => 1,
        ]);

        foreach ([
            ['112', true],
            ['911', false],
            ['113', false],
            ['118', false],
        ] as $i => [$text, $correct]) {
            $q2->options()->create([
                'option_text' => $text,
                'is_correct' => $correct,
                'order' => $i,
            ]);
        }
    }
}
