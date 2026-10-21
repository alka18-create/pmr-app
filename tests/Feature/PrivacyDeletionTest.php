<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Livewire\Members\MemberShow;
use App\Models\AuditLog;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PrivacyDeletionTest extends TestCase
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

    public function test_pengurus_dapat_menghapus_data_pribadi_anggota(): void
    {
        Storage::fake('public');
        $pengurus = $this->pengurus();
        $akun = User::factory()->create();
        $member = Member::factory()->create([
            'user_id' => $akun->id,
            'nisn' => '9988776655',
            'nis' => '11223344',
            'phone' => '081111222333',
            'address' => 'Jl. Rahasia No. 7',
            'email_pribadi' => 'siswa@privat.sch.id',
            'parent_name' => 'Budi Rahasia',
            'parent_phone' => '084444555666',
            'birth_place' => 'Kota Rahasia',
            'blood_type' => 'O',
            'photo_path' => 'photos/member.jpg',
        ]);
        $oldNisn = $member->nisn;
        $oldEmail = $akun->email;
        $oldName = $member->full_name;
        $oldPersonalData = [
            $oldName,
            $oldNisn,
            $member->nis,
            $member->phone,
            $member->address,
            $member->email_pribadi,
            $member->parent_name,
            $member->parent_phone,
            $member->birth_place,
            $member->birth_date?->toDateString(),
        ];

        Livewire::actingAs($pengurus)
            ->test(MemberShow::class, ['member' => $member])
            ->call('purgePersonalData')
            ->assertRedirect(route('members.index'));

        $purged = Member::withTrashed()->findOrFail($member->id);
        $purged->unsetRelation('user');

        $this->assertTrue($purged->trashed());
        $this->assertSame('Anggota Dihapus', $purged->full_name);
        $this->assertSame('anggota-dihapus-'.$member->id, $purged->slug);
        $this->assertNull($purged->nisn);
        $this->assertNull($purged->nis);
        $this->assertNull($purged->birth_date);
        $this->assertNull($purged->birth_place);
        $this->assertNull($purged->address);
        $this->assertNull($purged->phone);
        $this->assertNull($purged->email_pribadi);
        $this->assertNull($purged->parent_name);
        $this->assertNull($purged->parent_phone);
        $this->assertNull($purged->blood_type);
        $this->assertNull($purged->photo_path);
        $this->assertNull($purged->user_id);
        $this->assertNull($purged->notes);

        // Jejak NISN/nama lama tidak tersisa di tabel mana pun
        $this->assertFalse(DB::table('members')->where('nisn', $oldNisn)->exists());
        $this->assertFalse(DB::table('members')->where('phone', '081111222333')->exists());

        // Akun login dinonaktifkan & dianonimkan
        $akun->refresh();
        $this->assertSame(UserStatus::Inactive, $akun->status);
        $this->assertSame('Pengguna Dihapus', $akun->name);
        $this->assertStringStartsWith('dihapus-', $akun->email);
        $this->assertFalse(DB::table('users')->where('email', $oldEmail)->exists());

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'deleted',
            'model_id' => $member->id,
        ]);
        $log = AuditLog::where('model_id', $member->id)->first();
        $logPayload = json_encode([$log->old_values, $log->new_values]);

        foreach ($oldPersonalData as $value) {
            $this->assertStringNotContainsString((string) $value, $logPayload);
        }
    }

    public function test_anggota_biasa_ditolak_menghapus_data_pribadi(): void
    {
        $anggota = $this->anggota();
        $member = Member::factory()->create();

        $this->actingAs($anggota)
            ->get(route('members.show', $member))
            ->assertForbidden();

        $this->assertFalse(
            Gate::forUser($anggota)->allows('delete', $member)
        );
    }

    public function test_purge_member_tanpa_akun_login_tetap_jalan(): void
    {
        $pengurus = $this->pengurus();
        $member = Member::factory()->create(['user_id' => null]);

        Livewire::actingAs($pengurus)
            ->test(MemberShow::class, ['member' => $member])
            ->call('purgePersonalData')
            ->assertRedirect(route('members.index'));

        $purged = Member::withTrashed()->findOrFail($member->id);
        $this->assertTrue($purged->trashed());
        $this->assertSame('Anggota Dihapus', $purged->full_name);
        $this->assertNull($purged->user_id);
    }
}
