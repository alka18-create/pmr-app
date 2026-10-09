<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Livewire\Auth\Login;
use App\Livewire\Users\UserCreate;
use App\Livewire\Users\UserEdit;
use App\Livewire\Users\UserIndex;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => UserRole::Admin]);
    }

    public function test_admin_dapat_membuka_daftar_pengguna(): void
    {
        $admin = $this->admin();
        User::factory()->create(['name' => 'Budi Santoso']);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Budi Santoso')
            ->assertSee('Pengguna Baru');
    }

    public function test_non_admin_ditolak_membuka_halaman_pengguna(): void
    {
        foreach ([UserRole::Pengurus, UserRole::Anggota] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)->get(route('admin.users.index'))->assertForbidden();
            $this->actingAs($user)->get(route('admin.users.create'))->assertForbidden();
            $this->actingAs($user)->get(route('admin.users.edit', User::factory()->create()))->assertForbidden();
        }
    }

    public function test_admin_dapat_membuat_pengguna_baru(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(UserCreate::class)
            ->set('name', 'Citra Wijaya')
            ->set('email', 'citra@pmr.test')
            ->set('password', 'rahasia123')
            ->set('role', 'pengurus')
            ->set('status', 'active')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'citra@pmr.test',
            'role' => 'pengurus',
            'status' => 'active',
        ]);
    }

    public function test_validasi_form_pembuatan(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(UserCreate::class)
            ->set('name', '')
            ->set('email', 'bukan-email')
            ->set('password', 'pendek')
            ->set('role', 'superuser')
            ->call('save')
            ->assertHasErrors([
                'name' => 'Nama wajib diisi.',
                'email' => 'Format email tidak valid.',
                'password' => 'Password minimal 8 karakter.',
                'role' => 'Role tidak valid.',
            ]);

        $this->assertDatabaseCount('users', 1);
    }

    public function test_email_sudah_terdaftar_ditolak(): void
    {
        $admin = $this->admin();
        User::factory()->create(['email' => 'ada@pmr.test']);

        Livewire::actingAs($admin)
            ->test(UserCreate::class)
            ->set('name', 'Duplikat')
            ->set('email', 'ada@pmr.test')
            ->set('password', 'rahasia123')
            ->call('save')
            ->assertHasErrors(['email' => 'Email sudah terdaftar.']);
    }

    public function test_admin_dapat_memperbarui_pengguna_tanpa_password(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create(['name' => 'Nama Lama', 'role' => UserRole::Anggota]);
        $hashSebelum = $user->password;

        Livewire::actingAs($admin)
            ->test(UserEdit::class, ['user' => $user])
            ->set('name', 'Nama Baru')
            ->set('role', 'pengurus')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.users.index'));

        $user->refresh();
        $this->assertSame('Nama Baru', $user->name);
        $this->assertSame(UserRole::Pengurus, $user->role);
        $this->assertSame($hashSebelum, $user->password);
    }

    public function test_password_diupdate_bila_diisi(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create();

        Livewire::actingAs($admin)
            ->test(UserEdit::class, ['user' => $user])
            ->set('password', 'barubaru123')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertTrue(Hash::check('barubaru123', $user->refresh()->password));
    }

    public function test_admin_dapat_menonaktifkan_pengguna(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create(['status' => UserStatus::Active]);

        Livewire::actingAs($admin)
            ->test(UserIndex::class)
            ->call('toggleStatus', $user)
            ->assertSessionHas('success');

        $this->assertSame(UserStatus::Inactive, $user->refresh()->status);
    }

    public function test_admin_dapat_mengaktifkan_kembali_pengguna(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create(['status' => UserStatus::Inactive]);

        Livewire::actingAs($admin)
            ->test(UserIndex::class)
            ->call('toggleStatus', $user)
            ->assertSessionHas('success');

        $this->assertSame(UserStatus::Active, $user->refresh()->status);
    }

    public function test_pengguna_nonaktif_tidak_bisa_login(): void
    {
        $user = User::factory()->create(['status' => UserStatus::Inactive]);

        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'password')
            ->call('login')
            ->assertHasErrors(['email' => 'Email atau password salah.']);

        $this->assertGuest();
    }

    public function test_admin_tidak_bisa_menghapus_diri_sendiri(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(UserIndex::class)
            ->call('delete', $admin)
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_admin_dapat_menghapus_pengguna_lain(): void
    {
        $admin = $this->admin();
        $other = User::factory()->create();

        Livewire::actingAs($admin)
            ->test(UserIndex::class)
            ->call('delete', $other)
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $other->id]);
    }
}
