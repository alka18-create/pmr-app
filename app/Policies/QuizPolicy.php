<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Quiz;
use App\Models\User;

class QuizPolicy
{
    public function viewAny(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        return in_array($user->role, [UserRole::Anggota, UserRole::Pengurus, UserRole::Admin], true);
    }

    public function view(?User $user, Quiz $quiz): bool
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

    public function attempt(User $user, Quiz $quiz): bool
    {
        if (! in_array($user->role, [UserRole::Anggota, UserRole::Pengurus, UserRole::Admin], true)) {
            return false;
        }

        return $quiz->isWithinWindow();
    }
}
