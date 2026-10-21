<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Livewire\Livewire;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_forgot_password_page(): void
    {
        $response = $this->get('/lupa-password');
        $response->assertOk();
        $response->assertSee('Lupa Password');
    }

    public function test_guest_can_request_password_reset_link_via_post(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'siswa@sekolah.sch.id']);

        $response = $this->post('/lupa-password', [
            'email' => 'siswa@sekolah.sch.id',
        ]);

        $response->assertSessionHas('success');
        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_guest_can_request_password_reset_link_via_livewire(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'anggota@sekolah.sch.id']);

        Livewire::test(\App\Livewire\Auth\ForgotPassword::class)
            ->set('email', 'anggota@sekolah.sch.id')
            ->call('sendResetLink')
            ->assertHasNoErrors()
            ->assertSet('statusMessage', 'Tautan reset password berhasil dikirim ke email Anda.');

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_guest_can_view_reset_password_page_with_token(): void
    {
        $response = $this->get('/reset-password/sample-token?email=user@sekolah.sch.id');
        $response->assertOk();
        $response->assertSee('Reset Password');
        $response->assertSee('user@sekolah.sch.id');
    }

    public function test_user_can_reset_password_with_valid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'pmr@sekolah.sch.id',
            'password' => Hash::make('oldpassword123'),
        ]);

        $token = Password::broker()->createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => 'pmr@sekolah.sch.id',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'password_reset',
            'model_type' => $user->getMorphClass(),
            'model_id' => $user->id,
        ]);
    }

    public function test_reset_password_fails_with_invalid_token(): void
    {
        User::factory()->create([
            'email' => 'pmr@sekolah.sch.id',
            'password' => Hash::make('oldpassword123'),
        ]);

        $response = $this->post('/reset-password', [
            'token' => 'invalid-token-12345',
            'email' => 'pmr@sekolah.sch.id',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_reset_password_via_livewire_component(): void
    {
        $user = User::factory()->create([
            'email' => 'livewire@sekolah.sch.id',
            'password' => Hash::make('password_awal1'),
        ]);

        $token = Password::broker()->createToken($user);

        Livewire::test(\App\Livewire\Auth\ResetPassword::class, ['token' => $token])
            ->set('email', 'livewire@sekolah.sch.id')
            ->set('password', 'password_baru2')
            ->set('password_confirmation', 'password_baru2')
            ->call('resetPassword')
            ->assertHasNoErrors()
            ->assertRedirect(route('login'));

        $user->refresh();
        $this->assertTrue(Hash::check('password_baru2', $user->password));
    }
}
