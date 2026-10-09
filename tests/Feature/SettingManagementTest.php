<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Settings\OrganizationProfile;
use App\Livewire\Settings\SettingIndex;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class SettingManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => UserRole::Admin]);
    }

    public function test_admin_dapat_membuka_halaman_pengaturan(): void
    {
        $admin = $this->admin();
        Setting::set('org_tagline', 'Siaga & Peduli', 'general');

        $this->actingAs($admin)
            ->get(route('admin.settings.index'))
            ->assertOk()
            ->assertSee('Pengaturan')
            ->assertSee('org_tagline');
    }

    public function test_non_admin_ditolak_membuka_halaman_pengaturan(): void
    {
        foreach ([UserRole::Pengurus, UserRole::Anggota] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)->get(route('admin.settings.index'))->assertForbidden();
            $this->actingAs($user)->get(route('admin.settings.organization'))->assertForbidden();
        }
    }

    public function test_admin_dapat_menyimpan_pengaturan(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(SettingIndex::class)
            ->set('activeGroup', 'feature')
            ->set('key', 'enable_qr_checkin')
            ->set('value', 'true')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.settings.index', ['group' => 'feature']));

        $this->assertDatabaseHas('settings', [
            'key' => 'enable_qr_checkin',
            'value' => 'true',
            'group' => 'feature',
        ]);
    }

    public function test_validasi_form_pengaturan(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(SettingIndex::class)
            ->set('key', '')
            ->set('activeGroup', 'grup_salah')
            ->call('save')
            ->assertHasErrors([
                'key' => 'Key wajib diisi.',
                'group' => 'Group tidak valid.',
            ]);

        $this->assertDatabaseCount('settings', 0);
    }

    public function test_key_pengaturan_unik_ditolak(): void
    {
        $admin = $this->admin();
        Setting::set('sudah_ada', 'nilai_lama', 'general');

        Livewire::actingAs($admin)
            ->test(SettingIndex::class)
            ->set('key', 'sudah_ada')
            ->set('value', 'baru')
            ->call('save')
            ->assertHasErrors(['key' => 'Key sudah digunakan.']);
    }

    public function test_admin_dapat_menghapus_pengaturan(): void
    {
        $admin = $this->admin();
        $setting = Setting::set('bisa_dihapus', 'x', 'general');

        Livewire::actingAs($admin)
            ->test(SettingIndex::class)
            ->call('delete', $setting)
            ->assertRedirect(route('admin.settings.index', ['group' => 'general']))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('settings', ['id' => $setting->id]);
    }

    public function test_cache_invalidasi_saats_setting_diubah(): void
    {
        Setting::set('cache_key', 'v1', 'feature');
        $this->assertSame('v1', Setting::get('cache_key'));

        Setting::set('cache_key', 'v2', 'feature');
        $this->assertSame('v2', Setting::get('cache_key'));
    }

    public function test_get_mengembalikan_default_bila_key_tidak_ada(): void
    {
        $this->assertSame('fallback', Setting::get('tidak_ada', 'fallback'));
        $this->assertNull(Setting::get('juga_tidak_ada'));
    }

    public function test_admin_dapat_membuka_profil_organisasi(): void
    {
        $admin = $this->admin();
        Setting::set('org_name', 'PMR Wira SMA Contoh', 'general');

        $this->actingAs($admin)
            ->get(route('admin.settings.organization'))
            ->assertOk()
            ->assertSee('Profil Organisasi')
            ->assertSee('PMR Wira SMA Contoh');
    }

    public function test_admin_dapat_memperbarui_profil_organisasi(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(OrganizationProfile::class)
            ->set('name', 'PMR Wira SMA Negeri 1')
            ->set('history', 'Berdiri sejak 1998.')
            ->set('vision', 'Menjadi yang terdepan.')
            ->set('mission', 'Mengabdi dengan tulus.')
            ->set('email', 'pmr@sma.sch.id')
            ->set('phone', '081234567890')
            ->set('address', 'Jl. Merdeka No. 1')
            ->set('instagram', 'https://instagram.com/pmr')
            ->set('youtube', 'https://youtube.com/@pmr')
            ->set('tiktok', 'https://tiktok.com/@pmr')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.settings.organization'));

        $this->assertSame('PMR Wira SMA Negeri 1', Setting::get('org_name'));
        $this->assertSame('Berdiri sejak 1998.', Setting::get('org_history'));
        $this->assertSame('Menjadi yang terdepan.', Setting::get('org_vision'));
        $this->assertSame('Mengabdi dengan tulus.', Setting::get('org_mission'));
        $this->assertSame('pmr@sma.sch.id', Setting::get('contact_email'));
        $this->assertSame('081234567890', Setting::get('contact_phone'));
        $this->assertSame('Jl. Merdeka No. 1', Setting::get('contact_address'));
        $this->assertSame('https://instagram.com/pmr', Setting::get('social_instagram'));
        $this->assertSame('https://youtube.com/@pmr', Setting::get('social_youtube'));
        $this->assertSame('https://tiktok.com/@pmr', Setting::get('social_tiktok'));
    }

    public function test_validasi_form_profil_organisasi(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(OrganizationProfile::class)
            ->set('name', '')
            ->set('email', 'bukan-email')
            ->set('instagram', 'bukan-url')
            ->call('save')
            ->assertHasErrors([
                'name' => 'Nama organisasi wajib diisi.',
                'email' => 'Format email tidak valid.',
                'instagram' => 'Format URL Instagram tidak valid.',
            ]);
    }

    public function test_logo_valid_disimpan(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(OrganizationProfile::class)
            ->set('name', 'PMR Dengan Logo')
            ->set('logo', UploadedFile::fake()->image('logo.png'))
            ->call('save')
            ->assertHasNoErrors();

        $logoPath = Setting::get('org_logo');
        $this->assertNotEmpty($logoPath);
        Storage::disk('public')->assertExists($logoPath);
    }

    public function test_logo_tidak_valid_ditolak(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(OrganizationProfile::class)
            ->set('name', 'PMR')
            ->set('logo', UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf'))
            ->call('save')
            ->assertHasErrors(['logo' => 'Logo harus berupa gambar.']);

        $this->assertNull(Setting::get('org_logo'));
    }

    public function test_admin_dapat_mengubah_prinsip_dasar(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(OrganizationProfile::class)
            ->set('name', 'PMR Wira SMA Negeri 1')
            ->set('principles', [
                ['title' => 'Prinsip Kustom Satu', 'body' => 'Uraian kustom satu.'],
                ['title' => 'Prinsip Kustom Dua', 'body' => 'Uraian kustom dua.'],
            ])
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.settings.organization'));

        $stored = json_decode((string) Setting::get('org_principles'), true);
        $this->assertSame('Prinsip Kustom Satu', $stored[0]['title']);
        $this->assertSame('Uraian kustom dua.', $stored[1]['body']);
    }

    public function test_prinsip_kustom_tampil_di_halaman_profil(): void
    {
        Setting::set('org_principles', json_encode([
            ['title' => 'Prinsip Kustom Tampil', 'body' => 'Uraian tampil di publik.'],
        ]), 'general');

        $this->get(route('public.profile'))
            ->assertOk()
            ->assertSee('Prinsip Kustom Tampil')
            ->assertSee('Uraian tampil di publik.')
            ->assertDontSee('Kesemestaan');
    }

    public function test_prinsip_kosong_kembali_ke_redaksi_resmi(): void
    {
        Setting::set('org_principles', '[]', 'general');

        $this->get(route('public.profile'))
            ->assertOk()
            ->assertSee('7 Prinsip Dasar')
            ->assertSee('Kemanusiaan')
            ->assertSee('Kesemestaan');
    }
}
