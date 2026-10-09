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
});

Route::post('/logout', function (Request $request) {
    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()
        ->route('login')
        ->with('success', 'Anda telah keluar dari sistem.');
})->middleware('auth')->name('logout');
