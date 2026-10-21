<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_profile_page(): void
    {
        $response = $this->get('/panel/profil');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_profile_page(): void
    {
        $user = User::factory()->create([
            'name' => 'Siswa Teladan',
            'email' => 'siswa@sekolah.sch.id',
            'role' => UserRole::Anggota,
        ]);

        $response = $this->actingAs($user)->get('/panel/profil');
        $response->assertOk();
        $response->assertSee('Profil Saya');
        $response->assertSee('Siswa Teladan');
        $response->assertSee('siswa@sekolah.sch.id');
    }

    public function test_user_can_update_profile_info(): void
    {
        $user = User::factory()->create([
            'name' => 'Nama Lama',
            'email' => 'lama@sekolah.sch.id',
        ]);

        $this->actingAs($user);

        Livewire::test(\App\Livewire\Profile\ProfileIndex::class)
            ->set('name', 'Nama Baru')
            ->set('email', 'baru@sekolah.sch.id')
            ->call('saveProfile')
            ->assertHasNoErrors();

        $user->refresh();
        $this->assertSame('Nama Baru', $user->name);
        $this->assertSame('baru@sekolah.sch.id', $user->email);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'update',
            'model_type' => $user->getMorphClass(),
            'model_id' => $user->id,
        ]);
    }

    public function test_user_cannot_use_email_already_taken(): void
    {
        User::factory()->create(['email' => 'sudahada@sekolah.sch.id']);
        $user = User::factory()->create(['email' => 'saya@sekolah.sch.id']);

        $this->actingAs($user);

        Livewire::test(\App\Livewire\Profile\ProfileIndex::class)
            ->set('email', 'sudahada@sekolah.sch.id')
            ->call('saveProfile')
            ->assertHasErrors(['email']);
    }

    public function test_user_can_update_password_with_correct_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password_lama'),
        ]);

        $this->actingAs($user);

        Livewire::test(\App\Livewire\Profile\ProfileIndex::class)
            ->set('current_password', 'password_lama')
            ->set('password', 'password_baru123')
            ->set('password_confirmation', 'password_baru123')
            ->call('savePassword')
            ->assertHasNoErrors();

        $user->refresh();
        $this->assertTrue(Hash::check('password_baru123', $user->password));

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'update_password',
            'model_type' => $user->getMorphClass(),
            'model_id' => $user->id,
        ]);
    }

    public function test_user_cannot_update_password_with_wrong_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password_lama'),
        ]);

        $this->actingAs($user);

        Livewire::test(\App\Livewire\Profile\ProfileIndex::class)
            ->set('current_password', 'password_salah')
            ->set('password', 'password_baru123')
            ->set('password_confirmation', 'password_baru123')
            ->call('savePassword')
            ->assertHasErrors(['current_password']);
    }

    public function test_user_linked_to_member_can_view_identity_and_update_contact(): void
    {
        $user = User::factory()->create(['name' => 'Budi Santoso']);
        $member = Member::factory()->create([
            'user_id' => $user->id,
            'full_name' => 'Budi Santoso',
            'nis' => '12345',
            'nisn' => '0012345678',
            'address' => 'Alamat Awal',
            'phone' => '08123456789',
        ]);

        $this->actingAs($user);

        Livewire::test(\App\Livewire\Profile\ProfileIndex::class)
            ->assertSee('12345')
            ->assertSee('0012345678')
            ->set('address', 'Jl. Baru No. 10')
            ->set('phone', '08987654321')
            ->call('saveProfile')
            ->assertHasNoErrors();

        $member->refresh();
        $this->assertSame('Jl. Baru No. 10', $member->address);
        $this->assertSame('08987654321', $member->phone);
    }

    public function test_put_route_updates_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'Nama Put Awal',
            'email' => 'put@sekolah.sch.id',
        ]);

        $response = $this->actingAs($user)->put('/panel/profil', [
            'name' => 'Nama Put Baru',
            'email' => 'putbaru@sekolah.sch.id',
        ]);

        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertSame('Nama Put Baru', $user->name);
        $this->assertSame('putbaru@sekolah.sch.id', $user->email);
    }
}
