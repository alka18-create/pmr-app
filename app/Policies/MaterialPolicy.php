<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Material;
use App\Models\User;

class MaterialPolicy
{
    public function viewAny(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        return in_array($user->role, [UserRole::Anggota, UserRole::Pengurus, UserRole::Admin], true);
    }

    public function view(?User $user, Material $material): bool
    {
        if ($user === null) {
            return false;
        }

        return in_array($user->role, [UserRole::Anggota, UserRole::Pengurus, UserRole::Admin], true);
    }

    public function manage(User $user): bool
    {
        return in_array($user->role, [UserRole::Pengurus, UserRole::Admin], true);
    }
}
