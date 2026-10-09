<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        if (Article::query()->exists()) {
            return;
        }

        $author = User::query()->where('email', 'like', '%@pmr.test')->first()
            ?? User::factory()->create(['role' => 'pengurus']);

        Article::create([
            'title' => 'Selamat Datang di Website PMR',
            'excerpt' => 'Website resmi organisasi PMR Wira sekolah telah diluncurkan.',
            'content' => '<p>Kami dengan bangga meluncurkan website resmi PMR Wira. Melalui situs ini Anda dapat mengikuti kegiatan, membaca berita terbaru, dan melihat galeri dokumentasi.</p>',
            'category' => 'berita',
            'status' => 'published',
            'is_public' => true,
            'published_at' => now()->subDays(3),
            'author_id' => $author->id,
            'meta_title' => 'Selamat Datang di Website PMR',
            'meta_description' => 'Website resmi PMR Wira — berita, kegiatan, dan prestasi.',
        ]);

        Article::create([
            'title' => 'Draft Pengumuman Internal',
            'excerpt' => 'Pengumuman yang belum dipublikasikan.',
            'content' => '<p>Ini adalah draft yang tidak boleh tampil di publik.</p>',
            'category' => 'pengumuman',
            'status' => 'draft',
            'is_public' => true,
            'published_at' => null,
            'author_id' => $author->id,
        ]);
    }
}
