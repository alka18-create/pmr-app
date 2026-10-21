<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class SendPasswordResetLink
{
    public const MAX_ATTEMPTS = 5;

    public const DECAY_SECONDS = 900; // 15 menit

    /**
     * Kirim email tautan reset password ke user.
     *
     * @return string Status dari Password broker (RESET_LINK_SENT, INVALID_USER, atau 'throttled')
     */
    public function execute(string $email): string
    {
        $key = $this->throttleKey($email);

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            return 'throttled';
        }

        RateLimiter::hit($key, self::DECAY_SECONDS);

        $status = Password::broker()->sendResetLink(['email' => $email]);

        if ($status === Password::RESET_LINK_SENT) {
            $user = User::where('email', $email)->first();
            if ($user) {
                app(AuditLogger::class)->log('password_reset_requested', $user, [], [], $user);
            }
        }

        return $status;
    }

    public function secondsAvailable(string $email): int
    {
        return RateLimiter::availableIn($this->throttleKey($email));
    }

    private function throttleKey(string $email): string
    {
        return 'pwd-reset|'.Str::lower(trim($email)).'|'.request()->ip();
    }
}
