<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAreaTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dapat_membuka_area_admin_dengan_ringkasan(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'name' => 'Admin Utama']);

        $this->actingAs($admin)
            ->get(route('panel.admin'))
            ->assertOk()
            ->assertSee('Area Admin')
            ->assertSee('Total Pengguna')
            ->assertSee('Admin Utama')
            ->assertSee('Data Master')
            ->assertDontSee('Halaman placeholder');
    }

    public function test_pengurus_ditolak_membuka_area_admin(): void
    {
        $pengurus = User::factory()->create(['role' => UserRole::Pengurus]);

        $this->actingAs($pengurus)
            ->get(route('panel.admin'))
            ->assertForbidden();
    }

    public function test_tamu_dialihkan_ke_login(): void
    {
        $this->get(route('panel.admin'))->assertRedirect(route('login'));
    }
}
