<?php

namespace App\Actions\Users;

use App\Enums\UserStatus;
use App\Models\User;
use App\Services\AuditLogger;

class SetUserStatus
{
    public function execute(User $user, UserStatus $status): User
    {
        $old = ['status' => $user->status?->value];

        $user->status = $status;
        $user->save();

        app(AuditLogger::class)->log('updated', $user, $old, ['status' => $status->value]);

        return $user;
    }
}
