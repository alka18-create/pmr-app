<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Achievement;
use App\Models\User;

class AchievementPolicy
{
    public function view(?User $user, Achievement $achievement): bool
    {
        if ($achievement->isPubliclyVisible()) {
            return true;
        }

        if ($user === null) {
            return false;
        }

        return in_array($user->role, [UserRole::Pengurus, UserRole::Admin], true);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [UserRole::Pengurus, UserRole::Admin], true);
    }

    public function update(User $user, Achievement $achievement): bool
    {
        return in_array($user->role, [UserRole::Pengurus, UserRole::Admin], true);
    }

    public function delete(User $user, Achievement $achievement): bool
    {
        return in_array($user->role, [UserRole::Pengurus, UserRole::Admin], true);
    }
}
