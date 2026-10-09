<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\GalleryItem;
use App\Models\User;

class GalleryItemPolicy
{
    public function view(?User $user, GalleryItem $item): bool
    {
        if ($item->isPubliclyVisible()) {
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

    public function update(User $user, GalleryItem $item): bool
    {
        return in_array($user->role, [UserRole::Pengurus, UserRole::Admin], true);
    }

    public function delete(User $user, GalleryItem $item): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        return $user->role === UserRole::Pengurus && $item->uploaded_by === $user->id;
    }

    public function manageCategory(User $user): bool
    {
        return in_array($user->role, [UserRole::Pengurus, UserRole::Admin], true);
    }
}
