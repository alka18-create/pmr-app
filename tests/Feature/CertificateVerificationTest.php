<?php

namespace Tests\Feature;

use App\Enums\CertificateSourceType;
use App\Enums\UserRole;
use App\Livewire\Certificates\CertificateIndex;
use App\Livewire\Certificates\MyCertificates;
use App\Livewire\Website\CertificateVerify;
use App\Models\Certificate;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class CertificateVerificationTest extends TestCase
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

    public function test_verifikasi_publik_tanpa_login_valid(): void
    {
        $certificate = Certificate::factory()->create([
            'title' => 'Sertifikat P3K',
            'is_revoked' => false,
        ]);

        $this->get(route('verify.certificate', $certificate->code))
            ->assertOk()
            ->assertSee('Sertifikat Valid')
            ->assertSee($certificate->code)
            ->assertSee('Sertifikat P3K');
    }

    public function test_verifikasi_kode_tidak_ditemukan(): void
    {
        $this->get(route('verify.certificate', 'TIDAK-ADA-KODE-XYZ'))
            ->assertOk()
            ->assertSee('Tidak Ditemukan');
    }

    public function test_verifikasi_sertifikat_dicabut(): void
    {
        $certificate = Certificate::factory()->revoked()->create();

        $this->get(route('verify.certificate', $certificate->code))
            ->assertOk()
            ->assertSee('Sertifikat Dicabut');
    }

    public function test_kode_sertifikat_unik(): void
    {
        $codes = [];
        for ($i = 0; $i < 20; $i++) {
            $codes[] = Certificate::generateUniqueCode();
        }

        $this->assertSame(20, count(array_unique($codes)));
        $this->assertSame(20, count(array_filter($codes, fn ($c) => strlen($c) <= 32)));
    }

    public function test_pengurus_dapat_menerbitkan_dan_mencabut_sertifikat(): void
    {
        Storage::fake('public');
        $member = Member::factory()->create();

        Livewire::actingAs($this->pengurus())
            ->test(CertificateIndex::class)
            ->set([
                'issueMemberId' => $member->id,
                'issueSourceType' => 'event',
                'issueSourceId' => null,
                'issueTitle' => 'Sertifikat Kegiatan Bakti Sosial',
            ])
            ->call('issue')
            ->assertHasNoErrors()
            ->assertSessionHas('success');

        $certificate = Certificate::where('title', 'Sertifikat Kegiatan Bakti Sosial')->firstOrFail();
        $this->assertFalse($certificate->is_revoked);
        $this->assertSame($member->id, $certificate->member_id);
        $this->assertSame(CertificateSourceType::Event, $certificate->source_type);

        Livewire::actingAs($this->pengurus())
            ->test(CertificateIndex::class)
            ->call('revoke', $certificate->id)
            ->assertSessionHas('success');

        $this->assertTrue($certificate->fresh()->is_revoked);
    }

    public function test_terbit_ganda_untuk_sumber_sama_ditolak(): void
    {
        $member = Member::factory()->create();
        Certificate::factory()->create([
            'member_id' => $member->id,
            'source_type' => CertificateSourceType::Event,
            'source_id' => null,
            'is_revoked' => false,
        ]);

        Livewire::actingAs($this->pengurus())
            ->test(CertificateIndex::class)
            ->set([
                'issueMemberId' => $member->id,
                'issueSourceType' => 'event',
                'issueSourceId' => null,
                'issueTitle' => 'Duplikat',
            ])
            ->call('issue')
            ->assertHasErrors();

        $this->assertSame(1, Certificate::where('member_id', $member->id)->count());
    }

    public function test_anggota_ditolak_kelola_sertifikat(): void
    {
        $this->actingAs($this->anggota())
            ->get(route('certificates.manage'))
            ->assertForbidden();
    }

    public function test_anggota_hanya_lihat_sertifikat_sendiri(): void
    {
        $user = $this->anggota();
        $member = Member::factory()->create(['user_id' => $user->id]);
        $otherMember = Member::factory()->create();

        Certificate::factory()->create([
            'member_id' => $member->id,
            'title' => 'Sertifikat Milik Saya',
        ]);
        Certificate::factory()->create([
            'member_id' => $otherMember->id,
            'title' => 'Sertifikat Orang Lain',
        ]);

        Livewire::actingAs($user)
            ->test(MyCertificates::class)
            ->assertSee('Sertifikat Milik Saya')
            ->assertDontSee('Sertifikat Orang Lain');
    }

    public function test_halaman_verifikasi_tanpa_parameter(): void
    {
        $this->get(route('verify.certificate'))
            ->assertOk()
            ->assertSee('Verifikasi Sertifikat');
    }

    public function test_livewire_search_verifikasi(): void
    {
        $certificate = Certificate::factory()->create(['is_revoked' => false]);

        Livewire::test(CertificateVerify::class)
            ->set('code', $certificate->code)
            ->call('search')
            ->assertSee('Sertifikat Valid')
            ->assertSee($certificate->code);
    }
}
