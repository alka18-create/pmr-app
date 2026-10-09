<?php

namespace Tests\Feature;

use App\Livewire\Website\ContactPage;
use App\Models\Article;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class ContactAndSeoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('contact:127.0.0.1');
        RateLimiter::clear('contact::1');
    }

    public function test_beranda_profil_kontak_bisa_diakses_tanpa_login(): void
    {
        $this->get('/')->assertOk();
        $this->get(route('public.profile'))->assertOk();
        $this->get(route('public.contact'))->assertOk();
        $this->get(route('public.articles.index'))->assertOk();
        $this->get(route('public.gallery.index'))->assertOk();
        $this->get(route('public.achievements.index'))->assertOk();
    }

    public function test_form_kontak_menyimpan_pesan(): void
    {
        Livewire::test(ContactPage::class)
            ->set([
                'name' => 'Budi Santoso',
                'email' => 'budi@example.test',
                'subject' => 'Informasi Organisasi',
                'body' => 'Saya ingin bertanya tentang pendaftaran anggota baru.',
                'website' => '',
            ])
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('messages', [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.test',
            'subject' => 'Informasi Organisasi',
            'status' => 'unread',
        ]);
    }

    public function test_form_kontak_validasi_wajib(): void
    {
        Livewire::test(ContactPage::class)
            ->set(['name' => '', 'email' => 'bukan-email', 'subject' => '', 'body' => ''])
            ->call('submit')
            ->assertHasErrors(['name', 'email', 'subject', 'body']);
    }

    public function test_form_kontak_honeypot_ditolak_silent(): void
    {
        Livewire::test(ContactPage::class)
            ->set([
                'name' => 'Bot',
                'email' => 'bot@example.test',
                'subject' => 'Spam',
                'body' => 'Spam body content here.',
                'website' => 'https://spam.example',
            ])
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertSame(0, Message::count());
    }

    public function test_form_kontak_rate_limit(): void
    {
        for ($i = 0; $i < 5; $i++) {
            Livewire::test(ContactPage::class)
                ->set([
                    'name' => 'User '.$i,
                    'email' => "user{$i}@example.test",
                    'subject' => 'Subjek '.$i,
                    'body' => 'Pesan percobaan ke-'.$i,
                    'website' => '',
                ])
                ->call('submit');
        }

        $this->assertSame(5, Message::count());

        Livewire::test(ContactPage::class)
            ->set([
                'name' => 'User Batas',
                'email' => 'batas@example.test',
                'subject' => 'Subjek Batas',
                'body' => 'Pesan melewati batas rate limit.',
                'website' => '',
            ])
            ->call('submit')
            ->assertHasErrors(['body']);

        $this->assertSame(5, Message::count());
    }

    public function test_inbox_hanya_pengurus_admin(): void
    {
        $anggota = User::factory()->create(['role' => 'anggota']);
        $pengurus = User::factory()->create(['role' => 'pengurus']);

        $this->actingAs($anggota)->get(route('messages.index'))->assertForbidden();
        $this->actingAs($pengurus)->get(route('messages.index'))->assertOk();
    }

    public function test_sitemap_ada_dan_tidak_memuat_draft(): void
    {
        $published = Article::factory()->published()->create(['title' => 'Sitemap Publik Unik']);
        $draft = Article::factory()->draft()->create(['title' => 'Sitemap Draft Unik']);

        $response = $this->get(route('sitemap'));
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $response->assertSee($published->slug);
        $response->assertDontSee($draft->slug);
        $response->assertSee('/berita/', false);
    }

    public function test_robots_txt_ada(): void
    {
        $response = $this->get(route('robots'));
        $response->assertOk();
        $response->assertSee('User-agent:', false);
        $response->assertSee('Disallow: /panel/', false);
        $response->assertSee('sitemap.xml', false);
    }

    public function test_seo_meta_tampil_di_beranda_dan_berita(): void
    {
        $home = $this->get('/');
        $home->assertOk();
        $home->assertSee('name="description"', false);
        $home->assertSee('property="og:title"', false);
        $home->assertSee('rel="canonical"', false);

        $article = Article::factory()->published()->create([
            'title' => 'SEO Judul Unik Meta',
            'meta_description' => 'Deskripsi SEO unik untuk artikel.',
        ]);

        $detail = $this->get(route('public.articles.show', $article->slug));
        $detail->assertOk();
        $detail->assertSee('SEO Judul Unik Meta');
        $detail->assertSee('Deskripsi SEO unik untuk artikel.');
    }
}
