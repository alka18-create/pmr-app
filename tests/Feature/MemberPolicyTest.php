<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Http\Requests\MemberRequest;
use App\Models\Batch;
use App\Models\Member;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MemberPolicyTest extends TestCase
{
    use RefreshDatabase;

    private function member(): Member
    {
        return Member::factory()->create();
    }

    public function test_anggota_biasa_tidak_bisa_akses_data_internal(): void
    {
        $anggota = User::factory()->create(['role' => UserRole::Anggota]);
        $member = $this->member();

        $this->assertFalse(Gate::forUser($anggota)->allows('viewInternal', $member));
        $this->assertFalse(Gate::forUser($anggota)->allows('create', Member::class));
        $this->assertFalse(Gate::forUser($anggota)->allows('update', $member));
        $this->assertFalse(Gate::forUser($anggota)->allows('delete', $member));
        $this->assertFalse(Gate::forUser($anggota)->allows('import', Member::class));
        $this->assertFalse(Gate::forUser($anggota)->allows('export', Member::class));
    }

    public function test_pengurus_bisa_akses_data_internal_dan_crud(): void
    {
        $pengurus = User::factory()->create(['role' => UserRole::Pengurus]);
        $member = $this->member();

        $this->assertTrue(Gate::forUser($pengurus)->allows('viewInternal', $member));
        $this->assertTrue(Gate::forUser($pengurus)->allows('create', Member::class));
        $this->assertTrue(Gate::forUser($pengurus)->allows('update', $member));
        $this->assertTrue(Gate::forUser($pengurus)->allows('delete', $member));
        $this->assertTrue(Gate::forUser($pengurus)->allows('import', Member::class));
        $this->assertTrue(Gate::forUser($pengurus)->allows('export', Member::class));
    }

    public function test_admin_bisa_akses_semua_abilities_member(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $member = $this->member();

        foreach (['viewInternal', 'update', 'delete'] as $ability) {
            $this->assertTrue(Gate::forUser($admin)->allows($ability, $member), $ability);
        }

        foreach (['create', 'import', 'export'] as $ability) {
            $this->assertTrue(Gate::forUser($admin)->allows($ability, Member::class), $ability);
        }
    }

    public function test_semua_role_login_bisa_view_profil_publik(): void
    {
        $member = $this->member();

        foreach (UserRole::cases() as $role) {
            $user = User::factory()->create(['role' => $role]);
            $this->assertTrue(
                Gate::forUser($user)->allows('view', $member),
                "role {$role->value} harus bisa view publik"
            );
        }
    }

    public function test_member_request_menolak_nisn_duplikat(): void
    {
        Member::factory()->create(['nisn' => '1234567890']);

        $request = MemberRequest::create('/', 'POST', [
            'full_name' => 'Baru',
            'gender' => 'laki',
            'nisn' => '1234567890',
            'batch_id' => Batch::factory()->create()->id,
            'class_id' => SchoolClass::factory()->create()->id,
            'status' => 'aktif',
            'joined_at' => now()->toDateString(),
        ]);
        $request->setContainer($this->app);
        $request->setRedirector($this->app->make('redirect'));

        $this->expectException(ValidationException::class);

        $request->validateResolved();
    }
}
