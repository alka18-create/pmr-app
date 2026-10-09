<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PwaTest extends TestCase
{
    use RefreshDatabase;

    private function anggota(): User
    {
        return User::factory()->create(['role' => UserRole::Anggota]);
    }

    public function test_manifest_valid_dengan_icon_dan_start_url(): void
    {
        $path = public_path('manifest.json');
        $this->assertFileExists($path);

        $manifest = json_decode((string) file_get_contents($path), true);

        $this->assertIsArray($manifest);
        $this->assertSame('PMR Management System', $manifest['name']);
        $this->assertSame('/panel', $manifest['start_url']);
        $this->assertSame('standalone', $manifest['display']);
        $this->assertNotEmpty($manifest['icons']);

        foreach ($manifest['icons'] as $icon) {
            $this->assertFileExists(public_path(ltrim($icon['src'], '/')));
        }
    }

    public function test_service_worker_ada_dengan_handler_install_dan_fetch(): void
    {
        $path = public_path('sw.js');
        $this->assertFileExists($path);

        $sw = (string) file_get_contents($path);

        $this->assertStringContainsString("addEventListener('install'", $sw);
        $this->assertStringContainsString("addEventListener('activate'", $sw);
        $this->assertStringContainsString("addEventListener('fetch'", $sw);
        // Privasi: halaman panel tidak boleh masuk daftar pre-cache
        $this->assertStringNotContainsString("'/panel'", $sw);
        // Navigasi network-first dengan fallback offline
        $this->assertStringContainsString("request.mode === 'navigate'", $sw);
        $this->assertStringContainsString('Anda sedang offline.', $sw);
    }

    public function test_icon_png_valid(): void
    {
        foreach (['icons/icon-192.png', 'icons/icon-512.png', 'icons/apple-touch-icon.png'] as $icon) {
            $file = public_path($icon);
            $this->assertFileExists($file);
            $this->assertStringStartsWith("\x89PNG", (string) file_get_contents($file));
        }
    }

    public function test_panel_mendaftarkan_service_worker(): void
    {
        $user = $this->anggota();

        $this->actingAs($user)
            ->get(route('panel.home'))
            ->assertOk()
            ->assertSee('navigator.serviceWorker.register', false)
            ->assertSee('/manifest.json', false);
    }

    public function test_halaman_publik_tanpa_registrasi_service_worker(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('serviceWorker.register', false)
            ->assertSee('/manifest.json', false);
    }
}
