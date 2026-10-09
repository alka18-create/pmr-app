<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Achievement;
use App\Models\Article;
use App\Models\Event;
use App\Models\Member;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_profil_organisasi_menampilkan_7_prinsip(): void
    {
        Setting::set('org_history', 'Sejarah organisasi contoh.', 'general');
        Setting::set('org_vision', 'Visi organisasi.', 'general');
        Setting::set('org_mission', 'Misi organisasi.', 'general');

        $response = $this->get(route('public.profile'));
        $response->assertOk();
        $response->assertSee('Sejarah');
        $response->assertSee('Visi organisasi.');
        $response->assertSee('Misi organisasi.');
        $response->assertSee('7 Prinsip Dasar');
        $response->assertSee('Kemanusiaan');
        $response->assertSee('Kesukarelaan');
        $response->assertSee('Kesemestaan');
        $response->assertSee('Kenetralan');
        $response->assertSee('application/ld+json', false);
    }

    public function test_beranda_menampilkan_statistik_dan_konten_terbaru(): void
    {
        Member::factory()->create(['status' => 'aktif']);
        Event::factory()->published()->create(['title' => 'Kegiatan Home Unik']);
        Article::factory()->published()->create(['title' => 'Berita Home Unik']);
        Achievement::factory()->create(['title' => 'Prestasi Home Unik', 'is_public' => true]);

        $response = $this->get('/');
        $response->assertOk();
        $response->assertSee('Kegiatan Home Unik');
        $response->assertSee('Berita Home Unik');
        $response->assertSee('Prestasi Home Unik');
        $response->assertSee('Statistik');
    }

    public function test_konten_draft_tidak_muncul_di_beranda(): void
    {
        Article::factory()->draft()->create(['title' => 'Draft Home Unik']);
        Event::factory()->draft()->create(['title' => 'Event Draft Home Unik']);

        $response = $this->get('/');
        $response->assertOk();
        $response->assertDontSee('Draft Home Unik');
        $response->assertDontSee('Event Draft Home Unik');
    }

    public function test_navigation_publik_lengkap(): void
    {
        $response = $this->get('/');
        $response->assertOk();
        foreach (['Profil', 'Kegiatan', 'Berita', 'Galeri', 'Prestasi', 'Anggota', 'Kontak', 'Masuk'] as $label) {
            $response->assertSee('>'.$label.'<', false);
        }
    }

    public function test_anggota_biasa_bisa_buka_panel_konten(): void
    {
        $anggota = User::factory()->create(['role' => UserRole::Anggota]);

        foreach (['articles.index', 'articles.create', 'gallery.index', 'achievements.index', 'messages.index'] as $route) {
            $this->actingAs($anggota)->get(route($route))->assertForbidden();
        }
    }
}
