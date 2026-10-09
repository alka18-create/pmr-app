<?php

namespace App\Actions\Users;

use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Arr;

class UpdateUser
{
    public function execute(User $user, array $data): User
    {
        $oldAll = Arr::except($user->getAttributes(), ['password', 'remember_token']);

        if (empty($data['password'] ?? null)) {
            unset($data['password']);
        }

        $user->fill($data);
        $dirtyKeys = array_keys($user->getDirty());
        $user->save();

        $old = Arr::only($oldAll, $dirtyKeys);
        $new = Arr::only(
            Arr::except($user->getAttributes(), ['password', 'remember_token']),
            $dirtyKeys,
        );

        app(AuditLogger::class)->log('updated', $user, $old, $new);

        return $user;
    }
}
