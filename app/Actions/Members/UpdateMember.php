<?php

namespace App\Actions\Members;

use App\Models\Member;
use App\Services\AuditLogger;
use Illuminate\Support\Arr;

class UpdateMember
{
    public function execute(Member $member, array $data): Member
    {
        $oldAll = $member->getAttributes();

        $member->fill($data);

        if ($member->isDirty('full_name') && blank($member->slug)) {
            $member->slug = Member::generateUniqueSlug($member->full_name, $member->id);
        } elseif ($member->isDirty('full_name') && $member->wasChanged('slug') === false && ! isset($data['slug'])) {
            // slug tetap lama bila tidak diubah eksplisit — aman untuk edit
        }

        $dirtyKeys = array_keys($member->getDirty());
        $member->save();

        $old = Arr::only($oldAll, $dirtyKeys);
        $new = Arr::only($member->getAttributes(), $dirtyKeys);

        app(AuditLogger::class)->log('updated', $member, $old, $new);

        return $member;
    }
}
