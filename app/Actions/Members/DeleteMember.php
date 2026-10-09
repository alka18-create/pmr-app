<?php

namespace App\Actions\Members;

use App\Models\Member;
use App\Services\AuditLogger;
use Illuminate\Support\Arr;

class DeleteMember
{
    public function execute(Member $member): void
    {
        $old = $member->getAttributes();

        app(AuditLogger::class)->log('deleted', $member, Arr::except($old, ['notes']), []);

        $member->delete();
    }
}
