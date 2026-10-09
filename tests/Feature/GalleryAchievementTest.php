<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Achievements\AchievementIndex;
use App\Livewire\Gallery\GalleryIndex;
use App\Models\Achievement;
use App\Models\GalleryCategory;
use App\Models\GalleryItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Tests\TestCase;

class GalleryAchievementTest extends TestCase
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

    public function test_anggota_ditolak_panel_galeri_prestasi(): void
    {
        $anggota = $this->anggota();

        foreach (['gallery.index', 'achievements.index'] as $route) {
            $this->actingAs($anggota)->get(route($route))->assertForbidden();
        }
    }

    public function test_pengurus_dapat_buat_kategori_galeri(): void
    {
        Livewire::actingAs($this->pengurus())
            ->test(GalleryIndex::class)
            ->set([
                'newCategoryName' => 'Kegiatan Khusus',
                'newCategoryDescription' => 'Deskripsi kategori',
            ])
            ->call('saveCategory')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('gallery_categories', ['name' => 'Kegiatan Khusus']);
    }

    public function test_pengurus_dapat_tambah_item_video(): void
    {
        $pengurus = $this->pengurus();
        $category = GalleryCategory::factory()->create();

        Livewire::actingAs($pengurus)
            ->test(GalleryIndex::class)
            ->set([
                'title' => 'Video Latihan',
                'description' => 'Video YouTube',
                'category_id' => (string) $category->id,
                'file_type' => 'video',
                'video_url' => 'https://www.youtube.com/watch?v=abc123',
                'is_public' => true,
            ])
            ->call('saveItem')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('gallery_items', [
            'title' => 'Video Latihan',
            'file_type' => 'video',
            'uploaded_by' => $pengurus->id,
        ]);
    }

    public function test_item_galeri_image_butuh_file(): void
    {
        Livewire::actingAs($this->pengurus())
            ->test(GalleryIndex::class)
            ->set([
                'title' => 'Tanpa File',
                'file_type' => 'image',
            ])
            ->call('saveItem')
            ->assertHasErrors(['file']);
    }

    public function test_hapus_item_galeri_hanya_pembuat_atau_admin(): void
    {
        $uploader = $this->pengurus();
        $other = $this->pengurus();
        $item = GalleryItem::factory()->create(['uploaded_by' => $uploader->id]);

        $this->assertFalse(Gate::forUser($other)->allows('delete', $item));
        $this->assertTrue(Gate::forUser($uploader)->allows('delete', $item));
    }

    public function test_halaman_publik_galeri_hanya_public(): void
    {
        $public = GalleryItem::factory()->create([
            'title' => 'Galeri Publik Unik',
            'is_public' => true,
            'file_path' => null,
            'file_type' => 'video',
            'video_url' => 'https://www.youtube.com/watch?v=xyz',
        ]);
        $private = GalleryItem::factory()->privat()->create([
            'title' => 'Galeri Internal Unik',
            'file_path' => 'gallery/x.jpg',
        ]);

        $response = $this->get(route('public.gallery.index'));
        $response->assertOk();
        $response->assertSee($public->title);
        $response->assertDontSee($private->title);
    }

    public function test_halaman_publik_prestasi_hanya_public(): void
    {
        $public = Achievement::factory()->create(['title' => 'Prestasi Publik Unik', 'is_public' => true]);
        $private = Achievement::factory()->privat()->create(['title' => 'Prestasi Internal Unik']);

        $response = $this->get(route('public.achievements.index'));
        $response->assertOk();
        $response->assertSee($public->title);
        $response->assertDontSee($private->title);
    }

    public function test_kebijakan_achievement_create_update_hanya_pengurus_admin(): void
    {
        $anggota = $this->anggota();
        $pengurus = $this->pengurus();

        $this->assertFalse(Gate::forUser($anggota)->allows('create', Achievement::class));
        $this->assertTrue(Gate::forUser($pengurus)->allows('create', Achievement::class));
    }

    public function test_pengurus_dapat_tambah_prestasi(): void
    {
        $pengurus = $this->pengurus();

        Livewire::actingAs($pengurus)
            ->test(AchievementIndex::class)
            ->set([
                'title' => 'Juara 1 Lomba Kesehatan',
                'level' => 'kota',
                'category' => 'lomba',
                'year' => 2026,
                'description' => 'Juara tingkat kota.',
                'is_public' => true,
            ])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('achievements', [
            'title' => 'Juara 1 Lomba Kesehatan',
            'level' => 'kota',
            'is_public' => true,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'created',
            'user_id' => $pengurus->id,
        ]);
    }
}
