<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ResetPasswordWithToken
{
    /**
     * Reset password user berdasarkan token yang valid.
     *
     * @param  array{token: string, email: string, password: string, password_confirmation: string}  $credentials
     * @return string Status hasil reset (PASSWORD_RESET, INVALID_TOKEN, INVALID_USER, dll.)
     */
    public function execute(array $credentials): string
    {
        return Password::broker()->reset(
            $credentials,
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                app(AuditLogger::class)->log(
                    'password_reset',
                    $user,
                    [],
                    ['password_changed' => true],
                    $user,
                );
            }
        );
    }
}
