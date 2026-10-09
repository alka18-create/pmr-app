<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Member;
use App\Models\User;

class MemberPolicy
{
    public function view(User $user, Member $member): bool
    {
        return true;
    }

    public function viewInternal(User $user, Member $member): bool
    {
        return $user->role === UserRole::Pengurus || $user->role === UserRole::Admin;
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::Pengurus || $user->role === UserRole::Admin;
    }

    public function update(User $user, Member $member): bool
    {
        return $user->role === UserRole::Pengurus || $user->role === UserRole::Admin;
    }

    public function delete(User $user, Member $member): bool
    {
        return $user->role === UserRole::Pengurus || $user->role === UserRole::Admin;
    }

    public function import(User $user): bool
    {
        return $user->role === UserRole::Pengurus || $user->role === UserRole::Admin;
    }

    public function export(User $user): bool
    {
        return $user->role === UserRole::Pengurus || $user->role === UserRole::Admin;
    }
}
