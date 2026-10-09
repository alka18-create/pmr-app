<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Member;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PrivacyTest — menjamin data internal anggota tidak bocor ke publik.
 *
 * Lihat docs/10 §3.1.
 */
class PrivacyTest extends TestCase
{
    use RefreshDatabase;

    private const INTERNAL_SEED = [
        'nis' => '91000001',
        'nisn' => '9910000021',
        'birth_date' => '2010-03-14',
        'birth_place' => 'Kota Rahasia Privasi',
        'address' => 'Jl. Alamat Internal Unik 77',
        'phone' => '081299887766',
        'email_pribadi' => 'privat.anggota@example.test',
        'parent_name' => 'Ibu Orangtua Unik Privasi',
        'parent_phone' => '081255443322',
        'blood_type' => null,
        'notes' => 'Catatan internal sensitif unik-privasi',
    ];

    private function seedMemberWithInternalData(array $overrides = []): Member
    {
        return Member::factory()->create(array_merge(self::INTERNAL_SEED, $overrides));
    }

    private function assertNoInternalLeak(string $body, Member $member): void
    {
        $asserted = 0;

        foreach (Member::internalFields() as $field) {
            $value = $member->{$field};

            if ($value instanceof \DateTimeInterface) {
                $value = $value->format('Y-m-d');
            }

            if ($value === null || $value === '') {
                continue;
            }

            $value = (string) $value;

            // Lewati nilai terlalu pendek agar false-positive (mis. gol. darah "A").
            if (mb_strlen($value) < 6) {
                continue;
            }

            $this->assertStringNotContainsString(
                $value,
                $body,
                "Response publik tidak boleh mengandung nilai internal `{$field}` = {$value}"
            );
            $asserted++;
        }

        $this->assertGreaterThan(0, $asserted, 'Seed data internal minimal satu field panjang untuk diuji');
    }

    public function test_tamu_dapat_mengakses_beranda_tanpa_login(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_direktori_anggota_publik_tidak_mengandung_data_internal(): void
    {
        $member = $this->seedMemberWithInternalData(['full_name' => 'Siti Privasi Publik', 'slug' => null]);

        $response = $this->get(route('public.members.index'));
        $response->assertOk();
        $response->assertSee($member->full_name);
        $this->assertNoInternalLeak($response->getContent(), $member);
    }

    public function test_profil_publik_tidak_mengandung_data_internal(): void
    {
        $member = $this->seedMemberWithInternalData(['full_name' => 'Rani Profil Publik', 'slug' => null]);

        $response = $this->get(route('public.members.show', $member->slug));
        $response->assertOk();
        $response->assertSee($member->full_name);
        $response->assertSee($member->schoolClass->name);
        $this->assertNoInternalLeak($response->getContent(), $member);
    }

    public function test_profil_publik_hanya_menampilkan_field_publik(): void
    {
        $member = $this->seedMemberWithInternalData(['slug' => null]);
        $member->load(['batch', 'schoolClass', 'position']);

        $public = $member->toPublicArray();
        $this->assertEqualsCanonicalizing(
            ['id', 'full_name', 'slug', 'gender', 'photo_path', 'status', 'joined_at', 'batch', 'school_class', 'position'],
            array_keys($public)
        );

        $response = $this->get(route('public.members.show', $member->slug));
        $response->assertOk();

        foreach (Member::internalFields() as $field) {
            $this->assertArrayNotHasKey($field, $public);
        }

        $snapshotValues = $response->getContent();
        foreach (['nis', 'nisn', 'birth_date', 'birth_place', 'address', 'phone', 'email_pribadi', 'parent_name', 'parent_phone', 'notes'] as $internalKey) {
            $value = $member->{$internalKey};
            if ($value === null || $value === '') {
                continue;
            }
            if ($value instanceof \DateTimeInterface) {
                $value = $value->format('Y-m-d');
            }
            $this->assertStringNotContainsString((string) $value, $snapshotValues, "Livewire payload/body tidak boleh memuat {$internalKey}");
        }
    }

    public function test_alumni_dan_nonaktif_disembunyikan_dari_publik_secara_default(): void
    {
        $alumni = Member::factory()->alumni()->create(['full_name' => 'Alumni Tersembunyi', 'slug' => null]);
        $nonaktif = Member::factory()->nonaktif()->create(['full_name' => 'Nonaktif Tersembunyi', 'slug' => null]);

        $list = $this->get(route('public.members.index'));
        $list->assertOk();
        $list->assertDontSee($alumni->full_name);
        $list->assertDontSee($nonaktif->full_name);

        $this->get(route('public.members.show', $alumni->slug))->assertNotFound();
        $this->get(route('public.members.show', $nonaktif->slug))->assertNotFound();
    }

    public function test_alumni_tampil_bila_setting_public_show_alumni_aktif(): void
    {
        Setting::set('public_show_alumni', '1', 'feature');

        $alumni = Member::factory()->alumni()->create(['full_name' => 'Alumni Terlihat', 'slug' => null]);

        $this->get(route('public.members.show', $alumni->slug))->assertOk()->assertSee($alumni->full_name);
        $this->get(route('public.members.index'))->assertOk()->assertSee($alumni->full_name);
    }

    public function test_anggota_biasa_tidak_bisa_get_data_internal_anggota_lain(): void
    {
        $anggota = User::factory()->create(['role' => UserRole::Anggota]);
        $member = $this->seedMemberWithInternalData();

        $this->actingAs($anggota)
            ->get(route('members.show', $member))
            ->assertForbidden();
    }

    public function test_tamu_tidak_bisa_akses_route_panel_member(): void
    {
        $member = $this->seedMemberWithInternalData();

        $this->get(route('members.show', $member))->assertRedirect(route('login'));
        $this->get(route('members.index'))->assertRedirect(route('login'));
    }
}
