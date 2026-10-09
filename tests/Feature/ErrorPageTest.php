<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_403_menampilkan_halaman_kustom(): void
    {
        $user = User::factory()->create(['role' => UserRole::Anggota]);

        $this->actingAs($user)
            ->get(route('panel.admin'))
            ->assertForbidden()
            ->assertSee('Tidak Ada Akses');
    }

    public function test_404_menampilkan_halaman_kustom(): void
    {
        $this->get('/halaman-yang-tidak-ada')
            ->assertNotFound()
            ->assertSee('Halaman Tidak Ditemukan');
    }
}
