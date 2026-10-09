<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(UserRole $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get(route('panel.pengurus'))->assertRedirect(route('login'));
        $this->get(route('panel.admin'))->assertRedirect(route('login'));
    }

    public function test_anggota_dapat_membuka_dasarbor(): void
    {
        $user = $this->userWithRole(UserRole::Anggota);

        $this->actingAs($user)->get(route('panel.home'))->assertOk();
    }

    public function test_anggota_ditolak_di_area_pengurus_dan_admin(): void
    {
        $user = $this->userWithRole(UserRole::Anggota);

        $this->actingAs($user)->get(route('panel.pengurus'))->assertForbidden();
        $this->actingAs($user)->get(route('panel.admin'))->assertForbidden();
    }

    public function test_pengurus_dapat_membuka_area_pengurus_tapi_bukan_admin(): void
    {
        $user = $this->userWithRole(UserRole::Pengurus);

        $this->actingAs($user)->get(route('panel.pengurus'))->assertOk();
        $this->actingAs($user)->get(route('panel.admin'))->assertForbidden();
    }

    public function test_admin_dapat_membuka_semua_area(): void
    {
        $user = $this->userWithRole(UserRole::Admin);

        $this->actingAs($user)->get(route('panel.home'))->assertOk();
        $this->actingAs($user)->get(route('panel.pengurus'))->assertOk();
        $this->actingAs($user)->get(route('panel.admin'))->assertOk();
    }

    public function test_admin_melewati_semua_gate(): void
    {
        $admin = $this->userWithRole(UserRole::Admin);

        $this->assertTrue(Gate::forUser($admin)->allows('access-pengurus-area'));
        $this->assertTrue(Gate::forUser($admin)->allows('access-admin-area'));
    }

    public function test_gate_area_hanya_untuk_role_yang_berhak(): void
    {
        $pengurus = $this->userWithRole(UserRole::Pengurus);
        $this->assertTrue(Gate::forUser($pengurus)->allows('access-pengurus-area'));
        $this->assertFalse(Gate::forUser($pengurus)->allows('access-admin-area'));

        $anggota = $this->userWithRole(UserRole::Anggota);
        $this->assertFalse(Gate::forUser($anggota)->allows('access-pengurus-area'));
        $this->assertFalse(Gate::forUser($anggota)->allows('access-admin-area'));
    }
}
