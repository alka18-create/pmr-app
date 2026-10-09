<?php

namespace App\Actions\Members;

use App\Enums\MemberStatus;
use App\Models\Member;
use App\Services\AuditLogger;

class SetMemberStatus
{
    public function execute(Member $member, MemberStatus $status): Member
    {
        $old = ['status' => $member->status?->value];

        $member->status = $status;
        $member->save();

        app(AuditLogger::class)->log('updated', $member, $old, ['status' => $status->value]);

        return $member;
    }
}
