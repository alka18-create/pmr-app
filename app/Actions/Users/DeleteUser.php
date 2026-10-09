<?php

namespace App\Actions\Users;

use App\Models\User;
use App\Services\AuditLogger;
use DomainException;
use Illuminate\Support\Arr;

class DeleteUser
{
    public function execute(User $user, User $actor): void
    {
        if ($user->is($actor)) {
            throw new DomainException('Tidak bisa menghapus akun sendiri.');
        }

        $old = Arr::except($user->getAttributes(), ['password', 'remember_token']);

        app(AuditLogger::class)->log('deleted', $user, $old, []);

        $user->delete();
    }
}
