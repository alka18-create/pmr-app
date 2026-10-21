<?php

namespace Tests\Feature;

use App\Enums\CertificateSourceType;
use App\Enums\MaterialCategory;
use App\Enums\MaterialLevel;
use App\Enums\UserRole;
use App\Jobs\GenerateCertificate;
use App\Livewire\Achievements\AchievementIndex;
use App\Livewire\Materials\MaterialIndex;
use App\Models\Achievement;
use App\Models\Certificate;
use App\Models\Material;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Pengujian upload data sensitif di luar public storage (docs/08 §2.3).
 * Materi PDF, lampiran prestasi, dan sertifikat wajib disimpan di disk local (private).
 */
class SensitiveUploadStorageTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => UserRole::Admin]);
    }

    private function pengurus(): User
    {
        return User::factory()->create(['role' => UserRole::Pengurus]);
    }

    private function anggota(): User
    {
        return User::factory()->create(['role' => UserRole::Anggota]);
    }

    public function test_upload_materi_disimpan_di_disk_local_dan_bisa_didownload_anggota(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $admin = $this->admin();
        $file = UploadedFile::fake()->create('materi-darurat.pdf', 500, 'application/pdf');

        Livewire::actingAs($admin)
            ->test(MaterialIndex::class)
            ->set('title', 'Pertolongan Pertama Khusus')
            ->set('category', MaterialCategory::DasarP3k->value)
            ->set('level', MaterialLevel::Wira->value)
            ->set('order', 1)
            ->set('file', $file)
            ->call('save')
            ->assertHasNoErrors();

        $material = Material::where('title', 'Pertolongan Pertama Khusus')->firstOrFail();
        $this->assertNotNull($material->file_path);

        // Verifikasi tersimpan di disk local (private), BUKAN di disk public
        Storage::disk('local')->assertExists($material->file_path);
        Storage::disk('public')->assertMissing($material->file_path);

        // Tamu ditolak download materi
        auth()->logout();
        $this->get(route('materials.download', $material))->assertRedirect(route('login'));

        // Anggota login dapat mengakses materi
        $anggota = $this->anggota();
        $this->actingAs($anggota)
            ->get(route('materials.download', $material))
            ->assertOk();
    }

    public function test_sertifikat_disimpan_di_disk_local_dan_tidak_terekspos_di_public(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $member = Member::factory()->create();
        $certificate = Certificate::create([
            'code' => 'CERT-TEST-PRIV',
            'member_id' => $member->id,
            'source_type' => CertificateSourceType::Event,
            'source_id' => null,
            'title' => 'Sertifikat Latihan Gabungan',
            'issued_at' => now()->toDateString(),
            'is_revoked' => false,
        ]);

        (new GenerateCertificate($certificate->id))->handle();

        $certificate->refresh();
        $this->assertNotNull($certificate->file_path);

        // Verifikasi tersimpan di private local
        Storage::disk('local')->assertExists($certificate->file_path);
        Storage::disk('public')->assertMissing($certificate->file_path);
    }

    public function test_upload_lampiran_prestasi_disimpan_di_disk_local(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $pengurus = $this->pengurus();
        $file = UploadedFile::fake()->create('sk-prestasi.pdf', 300, 'application/pdf');

        Livewire::actingAs($pengurus)
            ->test(AchievementIndex::class)
            ->set('title', 'Juara 1 Pertolongan Pertama')
            ->set('level', 'kota')
            ->set('category', 'lomba')
            ->set('year', 2026)
            ->set('attachment', $file)
            ->call('save')
            ->assertHasNoErrors();

        $achievement = Achievement::where('title', 'Juara 1 Pertolongan Pertama')->firstOrFail();
        $this->assertNotNull($achievement->attachment_path);

        Storage::disk('local')->assertExists($achievement->attachment_path);
        Storage::disk('public')->assertMissing($achievement->attachment_path);

        // Pengurus dapat mengunduh lampiran
        $this->actingAs($pengurus)
            ->get(route('achievements.download', $achievement))
            ->assertOk();

        // Tamu ditolak
        auth()->logout();
        $this->get(route('achievements.download', $achievement))
            ->assertRedirect(route('login'));
    }
}
