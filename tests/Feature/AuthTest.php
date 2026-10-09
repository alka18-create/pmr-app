<?php

namespace Tests\Feature;

use App\Livewire\Auth\Login;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_tamu_dapat_melihat_halaman_login(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSeeLivewire(Login::class);
    }

    public function test_login_berhasil_mengarahkan_ke_dasbor(): void
    {
        $user = User::factory()->create();

        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'password')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('panel.home'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_password_salah_menampilkan_error(): void
    {
        $user = User::factory()->create();

        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'bukan-password')
            ->call('login')
            ->assertHasErrors(['email' => 'Email atau password salah.']);

        $this->assertGuest();
    }

    public function test_email_tidak_terdaftar_menampilkan_error_yang_sama(): void
    {
        Livewire::test(Login::class)
            ->set('email', 'belum-ada@pmr.test')
            ->set('password', 'apapun')
            ->call('login')
            ->assertHasErrors(['email' => 'Email atau password salah.']);

        $this->assertGuest();
    }

    public function test_login_dibatasi_5_percobaan_gagal(): void
    {
        $user = User::factory()->create();

        $component = Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'bukan-password');

        foreach (range(1, 5) as $attempt) {
            $component->call('login');
        }

        $component->call('login')
            ->assertHasErrors(['email'])
            ->assertSee('Terlalu banyak percobaan gagal');

        $this->assertGuest();
    }

    public function test_post_login_biasa_juga_berhasil(): void
    {
        $user = User::factory()->create();

        $this->post(route('login.attempt'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('panel.home'));

        $this->assertAuthenticatedAs($user);
        $this->get(route('panel.home'))
            ->assertOk()
            ->assertSee('Selamat datang kembali');
    }

    public function test_pengguna_login_tidak_bisa_membuka_login(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('login'))
            ->assertRedirect(route('panel.home'));
    }

    public function test_tamu_tidak_bisa_membuka_dasarbor(): void
    {
        $this->get(route('panel.home'))->assertRedirect(route('login'));
    }

    public function test_logout_mengembalikan_ke_tamu(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Anda telah keluar');
    }

    public function test_logout_butuh_login(): void
    {
        $this->post(route('logout'))->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
