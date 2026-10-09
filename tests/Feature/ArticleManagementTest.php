<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Articles\ArticleCreate;
use App\Livewire\Articles\ArticleEdit;
use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Tests\TestCase;

class ArticleManagementTest extends TestCase
{
    use RefreshDatabase;

    private function pengurus(): User
    {
        return User::factory()->create(['role' => UserRole::Pengurus]);
    }

    private function anggota(): User
    {
        return User::factory()->create(['role' => UserRole::Anggota]);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => UserRole::Admin]);
    }

    public function test_anggota_ditolak_halaman_artikel_panel(): void
    {
        $anggota = $this->anggota();

        foreach (['articles.index', 'articles.create'] as $route) {
            $this->actingAs($anggota)->get(route($route))->assertForbidden();
        }
    }

    public function test_pengurus_dapat_buat_artikel(): void
    {
        $pengurus = $this->pengurus();

        Livewire::actingAs($pengurus)
            ->test(ArticleCreate::class)
            ->set([
                'title' => 'Berita PMR Terbaru',
                'excerpt' => 'Ringkasan berita',
                'content' => '<p>Isi konten artikel.</p>',
                'category' => 'berita',
                'status' => 'published',
                'is_public' => true,
                'meta_title' => 'Meta Judul',
                'meta_description' => 'Meta deskripsi singkat.',
            ])
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('articles', [
            'title' => 'Berita PMR Terbaru',
            'status' => 'published',
            'author_id' => $pengurus->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'created',
            'user_id' => $pengurus->id,
        ]);
    }

    public function test_artikel_wajib_judul_dan_konten(): void
    {
        Livewire::actingAs($this->pengurus())
            ->test(ArticleCreate::class)
            ->set(['title' => '', 'content' => ''])
            ->call('save')
            ->assertHasErrors(['title', 'content']);
    }

    public function test_pengurus_dapat_edit_artikel(): void
    {
        $pengurus = $this->pengurus();
        $article = Article::factory()->create(['author_id' => $pengurus->id]);

        Livewire::actingAs($pengurus)
            ->test(ArticleEdit::class, ['article' => $article])
            ->set([
                'title' => 'Judul Diperbarui',
                'content' => '<p>Konten baru.</p>',
                'category' => 'pengumuman',
                'status' => 'published',
            ])
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'title' => 'Judul Diperbarui',
        ]);
    }

    public function test_hapus_artikel_hanya_admin_atau_penulis(): void
    {
        $author = $this->pengurus();
        $other = $this->pengurus();
        $admin = $this->admin();
        $article = Article::factory()->create(['author_id' => $author->id]);

        $this->assertFalse(Gate::forUser($other)->allows('delete', $article));
        $this->assertTrue(Gate::forUser($author)->allows('delete', $article));
        $this->assertTrue(Gate::forUser($admin)->allows('delete', $article));

        Livewire::actingAs($author)
            ->test(ArticleEdit::class, ['article' => $article])
            ->set(['title' => 'X', 'content' => 'Y', 'category' => 'berita', 'status' => 'draft'])
            ->call('save');

        $this->assertDatabaseHas('articles', ['id' => $article->id]);
    }

    public function test_halaman_publik_berita_hanya_published_dan_public(): void
    {
        $published = Article::factory()->published()->create(['title' => 'Berita Publik Terlihat']);
        $draft = Article::factory()->draft()->create(['title' => 'Draft Tersembunyi']);
        $private = Article::factory()->published()->privat()->create(['title' => 'Internal Tersembunyi']);
        $future = Article::factory()->published()->create([
            'title' => 'Masa Depan Tersembunyi',
            'published_at' => now()->addDay(),
        ]);

        $response = $this->get(route('public.articles.index'));
        $response->assertOk();
        $response->assertSee($published->title);
        $response->assertDontSee($draft->title);
        $response->assertDontSee($private->title);
        $response->assertDontSee($future->title);

        $this->get(route('public.articles.show', $draft->slug))->assertNotFound();
        $this->get(route('public.articles.show', $private->slug))->assertNotFound();
        $this->get(route('public.articles.show', $published->slug))->assertOk()->assertSee($published->title);
    }

    public function test_index_panel_artikel_hanya_pengurus_admin(): void
    {
        $this->actingAs($this->pengurus())->get(route('articles.index'))->assertOk();
        $this->actingAs($this->admin())->get(route('articles.index'))->assertOk();
        $this->actingAs($this->anggota())->get(route('articles.index'))->assertForbidden();
    }

    public function test_sanitasi_html_konten_artikel(): void
    {
        $pengurus = $this->pengurus();

        Livewire::actingAs($pengurus)
            ->test(ArticleCreate::class)
            ->set([
                'title' => 'XSS Test',
                'content' => '<p>Aman</p><script>alert(1)</script><p onclick="evil()">Klik</p>',
                'category' => 'berita',
                'status' => 'draft',
            ])
            ->call('save')
            ->assertHasNoErrors();

        $article = Article::where('title', 'XSS Test')->firstOrFail();
        $this->assertStringNotContainsString('<script>', $article->content);
        $this->assertStringNotContainsString('onclick', $article->content);
        $this->assertStringContainsString('<p>Aman</p>', $article->content);
    }
}
