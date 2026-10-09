<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanelLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_melihat_menu_lengkap(): void
    {
        $user = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($user)
            ->get(route('panel.home'))
            ->assertOk()
            ->assertSee('Dasbor')
            ->assertSee('Area Pengurus')
            ->assertSee('Area Admin');
    }

    public function test_pengurus_melihat_menu_pengurus_tapi_bukan_admin(): void
    {
        $user = User::factory()->create(['role' => UserRole::Pengurus]);

        $this->actingAs($user)
            ->get(route('panel.home'))
            ->assertOk()
            ->assertSee('Area Pengurus')
            ->assertDontSee('Area Admin');
    }

    public function test_anggota_tidak_melihat_menu_area_terbatas(): void
    {
        $user = User::factory()->create(['role' => UserRole::Anggota]);

        $this->actingAs($user)
            ->get(route('panel.home'))
            ->assertOk()
            ->assertSee('Dasbor')
            ->assertDontSee('Area Pengurus')
            ->assertDontSee('Area Admin');
    }

    public function test_area_menampilkan_identitas_pengguna(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Pengurus,
            'name' => 'Budi Pengurus',
        ]);

        $this->actingAs($user)
            ->get(route('panel.pengurus'))
            ->assertOk()
            ->assertSee('Area Pengurus')
            ->assertSee('Budi Pengurus')
            ->assertSee('pengurus');
    }
}
