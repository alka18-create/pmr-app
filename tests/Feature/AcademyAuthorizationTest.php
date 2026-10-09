<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademyAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private function user(UserRole $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    public function test_tamu_tidak_bisa_akses_panel_akademi(): void
    {
        foreach (['materials.index', 'quizzes.index', 'results.index', 'certificates.mine'] as $route) {
            $this->get(route($route))->assertRedirect(route('login'));
        }
    }

    public function test_anggota_ditolak_route_manage_akademi(): void
    {
        $anggota = $this->user(UserRole::Anggota);

        foreach (['materials.manage', 'quizzes.create', 'certificates.manage'] as $route) {
            $this->actingAs($anggota)->get(route($route))->assertForbidden();
        }
    }

    public function test_pengurus_dapat_akses_route_manage_akademi(): void
    {
        $pengurus = $this->user(UserRole::Pengurus);

        foreach (['materials.manage', 'quizzes.create', 'certificates.manage'] as $route) {
            $this->actingAs($pengurus)->get(route($route))->assertOk();
        }
    }

    public function test_anggota_dapat_akses_route_akademi(): void
    {
        $anggota = $this->user(UserRole::Anggota);

        foreach (['materials.index', 'quizzes.index', 'results.index', 'certificates.mine'] as $route) {
            $this->actingAs($anggota)->get(route($route))->assertOk();
        }
    }

    public function test_verifikasi_publik_tidak_butuh_login(): void
    {
        $this->get('/verifikasi/TEST-KODE-123')->assertOk();
    }
}
