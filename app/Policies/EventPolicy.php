<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    public function view(?User $user, Event $event): bool
    {
        if ($event->isPubliclyVisible()) {
            return true;
        }

        if ($user === null) {
            return false;
        }

        return in_array($user->role, [UserRole::Anggota, UserRole::Pengurus, UserRole::Admin], true);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [UserRole::Pengurus, UserRole::Admin], true);
    }

    public function update(User $user, Event $event): bool
    {
        return in_array($user->role, [UserRole::Pengurus, UserRole::Admin], true);
    }

    public function delete(User $user, Event $event): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        return $user->role === UserRole::Pengurus && $event->created_by === $user->id;
    }
}
