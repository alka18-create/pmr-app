<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\AttendanceRecord;
use App\Models\User;

class AttendancePolicy
{
    public function manage(User $user): bool
    {
        return in_array($user->role, [UserRole::Pengurus, UserRole::Admin], true);
    }

    public function viewOwn(User $user): bool
    {
        return in_array($user->role, [UserRole::Anggota, UserRole::Pengurus, UserRole::Admin], true);
    }

    public function checkInOwn(User $user): bool
    {
        return in_array($user->role, [UserRole::Anggota, UserRole::Pengurus, UserRole::Admin], true);
    }

    public function update(User $user, AttendanceRecord $record): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        if ($user->role !== UserRole::Pengurus) {
            return false;
        }

        return $record->event?->created_by === $user->id;
    }
}
