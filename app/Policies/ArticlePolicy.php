<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Article;
use App\Models\User;

class ArticlePolicy
{
    public function view(?User $user, Article $article): bool
    {
        if ($article->isPubliclyVisible()) {
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

    public function update(User $user, Article $article): bool
    {
        return in_array($user->role, [UserRole::Pengurus, UserRole::Admin], true);
    }

    public function delete(User $user, Article $article): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        return $user->role === UserRole::Pengurus && $article->author_id === $user->id;
    }
}
