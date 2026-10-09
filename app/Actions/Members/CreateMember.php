<?php

namespace App\Actions\Members;

use App\Models\Member;
use App\Services\AuditLogger;
use Illuminate\Support\Arr;

class CreateMember
{
    public function execute(array $data): Member
    {
        $member = Member::create($data);

        app(AuditLogger::class)->log(
            'created',
            $member,
            [],
            Arr::except($member->getAttributes(), ['notes']),
        );

        return $member;
    }
}
