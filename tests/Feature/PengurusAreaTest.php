<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Event;
use App\Models\Member;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengurusAreaTest extends TestCase
{
    use RefreshDatabase;

    public function test_pengurus_dapat_membuka_area_pengurus_dengan_ringkasan(): void
    {
        $user = User::factory()->create(['role' => UserRole::Pengurus]);
        Member::factory()->create(['status' => 'aktif']);
        Event::factory()->published()->create(['title' => 'Kegiatan Pengurus Unik']);
        Message::factory()->create(['subject' => 'Pesan Pengurus Unik']);

        $this->actingAs($user)
            ->get(route('panel.pengurus'))
            ->assertOk()
            ->assertSee('Area Pengurus')
            ->assertSee('Anggota Aktif')
            ->assertSee('Kegiatan Pengurus Unik')
            ->assertSee('Pesan Pengurus Unik')
            ->assertDontSee('Halaman placeholder');
    }

    public function test_anggota_ditolak_membuka_area_pengurus(): void
    {
        $user = User::factory()->create(['role' => UserRole::Anggota]);

        $this->actingAs($user)
            ->get(route('panel.pengurus'))
            ->assertForbidden();
    }

    public function test_tamu_dialihkan_ke_login(): void
    {
        $this->get(route('panel.pengurus'))->assertRedirect(route('login'));
    }
}
