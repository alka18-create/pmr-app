<?php

namespace App\Actions\Profile;

use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UpdatePassword
{
    /**
     * Ganti password user dengan validasi password saat ini.
     */
    public function execute(User $user, string $currentPassword, string $newPassword): void
    {
        if (! Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Password saat ini tidak sesuai.',
            ]);
        }

        $user->forceFill([
            'password' => Hash::make($newPassword),
        ])->save();

        app(AuditLogger::class)->log(
            'update_password',
            $user,
            [],
            ['password_changed' => true],
            $user,
        );
    }
}
