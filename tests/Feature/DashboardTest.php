<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Event;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dasbor_menampilkan_statistik_anggota_dan_kegiatan(): void
    {
        Member::factory()->count(3)->create();
        Event::factory()->count(2)->create();
        $pengurus = User::factory()->create(['role' => UserRole::Pengurus]);

        $this->actingAs($pengurus)
            ->get(route('panel.home'))
            ->assertOk()
            ->assertSee('Anggota')
            ->assertSee('Kegiatan')
            ->assertSee('Anggota per Kelas');
    }

    public function test_dasbor_menampilkan_shortcut_untuk_pengurus(): void
    {
        $pengurus = User::factory()->create(['role' => UserRole::Pengurus]);

        $this->actingAs($pengurus)
            ->get(route('panel.home'))
            ->assertOk()
            ->assertSee('Tambah Anggota')
            ->assertSee('Buat Kegiatan');
    }

    public function test_dasbor_anggota_tanpa_statistik_internal(): void
    {
        $anggota = User::factory()->create(['role' => UserRole::Anggota]);

        $this->actingAs($anggota)
            ->get(route('panel.home'))
            ->assertOk()
            ->assertSee('Dasbor')
            ->assertDontSee('Anggota per Kelas')
            ->assertDontSee('Tambah Anggota');
    }
}
