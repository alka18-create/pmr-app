<?php

namespace App\Actions\Auth;

use App\Enums\Auth\LoginResult;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AttemptLogin
{
    public const MAX_ATTEMPTS = 5;

    public const DECAY_SECONDS = 900;

    public function attempt(string $email, string $password, bool $remember = false): LoginResult
    {
        $key = $this->key($email);

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            return LoginResult::Throttled;
        }

        $success = Auth::attempt([
            'email' => $email,
            'password' => $password,
            'status' => 'active',
        ], $remember);

        if (! $success) {
            RateLimiter::hit($key, self::DECAY_SECONDS);

            return LoginResult::Failed;
        }

        RateLimiter::clear($key);
        session()->regenerate();

        app(AuditLogger::class)->log('login', Auth::user(), [], [], Auth::user());

        return LoginResult::Success;
    }

    public function secondsAvailable(string $email): int
    {
        return RateLimiter::availableIn($this->key($email));
    }

    private function key(string $email): string
    {
        return Str::lower(trim($email)).'|'.request()->ip();
    }
}
