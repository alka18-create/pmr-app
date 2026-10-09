<?php

namespace App\Actions\Members;

use App\Enums\MemberStatus;
use App\Models\Batch;
use App\Models\Member;

/**
 * Bulk tandai alumni per angkatan (docs/13 §7.3).
 * Hanya member berstatus aktif; audit log per member via SetMemberStatus.
 */
class MarkBatchAsAlumni
{
    public function execute(Batch $batch): int
    {
        $count = 0;

        Member::query()
            ->where('batch_id', $batch->id)
            ->where('status', MemberStatus::Aktif->value)
            ->orderBy('id')
            ->each(function (Member $member) use (&$count) {
                app(SetMemberStatus::class)->execute($member, MemberStatus::Alumni);
                $count++;
            });

        return $count;
    }
}
