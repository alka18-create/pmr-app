<?php

use App\Actions\Auth\AttemptLogin;
use App\Enums\Auth\LoginResult;
use App\Livewire\Auth\Login;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');

    Route::post('/login', function (Request $request, AttemptLogin $attempt) {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $result = $attempt->attempt($data['email'], $data['password'], $request->boolean('remember'));

        return match ($result) {
            LoginResult::Success => redirect()
                ->intended(route('panel.home'))
                ->with('success', 'Selamat datang kembali.'),
            LoginResult::Throttled => back()->withErrors([
                'email' => sprintf(
                    'Terlalu banyak percobaan gagal. Coba lagi dalam %d detik.',
                    $attempt->secondsAvailable($data['email']),
                ),
            ])->onlyInput('email'),
            LoginResult::Failed => back()->withErrors([
                'email' => 'Email atau password salah.',
            ])->onlyInput('email'),
        };
    })->name('login.attempt');

    Route::get('/lupa-password', \App\Livewire\Auth\ForgotPassword::class)->name('password.request');

    Route::post('/lupa-password', function (Request $request, \App\Actions\Auth\SendPasswordResetLink $sender) {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);

        $status = $sender->execute($data['email']);

        if ($status === 'throttled') {
            return back()->withErrors([
                'email' => sprintf('Terlalu banyak permintaan. Coba lagi dalam %d detik.', $sender->secondsAvailable($data['email'])),
            ])->onlyInput('email');
        }

        return back()->with('success', 'Jika email terdaftar, tautan reset password telah dikirimkan ke kotak masuk Anda.');
    })->name('password.email');

    Route::get('/reset-password/{token}', \App\Livewire\Auth\ResetPassword::class)->name('password.reset');

    Route::post('/reset-password', function (Request $request, \App\Actions\Auth\ResetPasswordWithToken $resetter) {
        $credentials = $request->validate([
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

        $status = $resetter->execute($credentials);

        if ($status === \Illuminate\Support\Facades\Password::PASSWORD_RESET) {
            return redirect()
                ->route('login')
                ->with('success', 'Password Anda berhasil diperbarui. Silakan masuk.');
        }

        return back()->withErrors([
            'email' => 'Tautan reset password tidak valid atau sudah kedaluwarsa.',
        ])->onlyInput('email');
    })->name('password.update');
});

Route::post('/logout', function (Request $request) {
    $user = Auth::user();
    if ($user) {
        app(\App\Services\AuditLogger::class)->log('logout', $user, [], [], $user);
    }

    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()
        ->route('login')
        ->with('success', 'Anda telah keluar dari sistem.');
})->middleware('auth')->name('logout');
