<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Certificate;
use App\Models\User;

class CertificatePolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [UserRole::Pengurus, UserRole::Admin], true);
    }

    public function viewOwn(User $user, Certificate $certificate): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        return $certificate->member?->user_id === $user->id
            || in_array($user->role, [UserRole::Pengurus], true);
    }

    public function verify(?User $user, Certificate $certificate): bool
    {
        return true;
    }

    public function manage(User $user): bool
    {
        return in_array($user->role, [UserRole::Pengurus, UserRole::Admin], true);
    }
}
