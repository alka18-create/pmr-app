<?php

namespace App\Livewire\Auth;

use App\Actions\Auth\SendPasswordResetLink;
use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.guest', ['title' => 'Lupa Password'])]
class ForgotPassword extends Component
{
    public string $email = '';

    public ?string $statusMessage = null;

    public function sendResetLink(SendPasswordResetLink $sender): void
    {
        $this->validate([
            'email' => ['required', 'email', 'max:255'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);

        $status = $sender->execute($this->email);

        if ($status === 'throttled') {
            $this->addError('email', sprintf(
                'Terlalu banyak permintaan. Silakan coba lagi dalam %d detik.',
                $sender->secondsAvailable($this->email),
            ));

            return;
        }

        if ($status === Password::RESET_LINK_SENT) {
            $this->statusMessage = 'Tautan reset password berhasil dikirim ke email Anda.';
            $this->email = '';
        } else {
            // Mencegah enumerasi email: tetap beri respons netral atau feedback trans
            $this->statusMessage = 'Jika email terdaftar, tautan reset password telah dikirimkan ke kotak masuk Anda.';
            $this->email = '';
        }
    }

    public function render()
    {
        return view('livewire.auth.forgot-password');
    }
}
