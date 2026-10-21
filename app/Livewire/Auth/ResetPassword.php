<?php

namespace App\Livewire\Auth;

use App\Actions\Auth\ResetPasswordWithToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.guest', ['title' => 'Reset Password'])]
class ResetPassword extends Component
{
    #[Locked]
    public string $token = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(string $token, Request $request): void
    {
        $this->token = $token;
        $this->email = (string) $request->query('email', '');
    }

    public function resetPassword(ResetPasswordWithToken $resetter): void
    {
        $this->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $status = $resetter->execute([
            'token' => $this->token,
            'email' => $this->email,
            'password' => $this->password,
            'password_confirmation' => $this->password_confirmation,
        ]);

        if ($status === Password::PASSWORD_RESET) {
            session()->flash('success', 'Password Anda berhasil diperbarui. Silakan masuk.');
            $this->redirect(route('login'), navigate: true);

            return;
        }

        $this->addError('email', 'Tautan reset password tidak valid atau sudah kedaluwarsa.');
    }

    public function render()
    {
        return view('livewire.auth.reset-password');
    }
}
