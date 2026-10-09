<?php

namespace App\Livewire\Auth;

use App\Actions\Auth\AttemptLogin;
use App\Enums\Auth\LoginResult;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.guest')]
class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function login(AttemptLogin $attempt): void
    {
        $this->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $result = $attempt->attempt($this->email, $this->password, $this->remember);

        match ($result) {
            LoginResult::Success => redirect()
                ->intended(route('panel.home'))
                ->with('success', 'Selamat datang kembali.'),
            LoginResult::Throttled => $this->addError('email', sprintf(
                'Terlalu banyak percobaan gagal. Coba lagi dalam %d detik.',
                $attempt->secondsAvailable($this->email),
            )),
            LoginResult::Failed => $this->addError('email', 'Email atau password salah.'),
        };
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
