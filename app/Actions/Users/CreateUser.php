<?php

namespace App\Actions\Users;

use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Arr;

class CreateUser
{
    public function execute(array $data): User
    {
        $user = User::create($data);

        app(AuditLogger::class)->log(
            'created',
            $user,
            [],
            Arr::except($data, ['password']),
        );

        return $user;
    }
}
