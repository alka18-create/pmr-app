<?php

namespace App\Actions\Achievements;

use App\Models\Achievement;
use App\Services\AuditLogger;

class UpdateAchievement
{
    public function execute(Achievement $achievement, array $data, ?\Illuminate\Http\UploadedFile $attachment = null): Achievement
    {
        if ($attachment) {
            if ($achievement->attachment_path) {
                \Illuminate\Support\Facades\Storage::disk('local')->delete($achievement->attachment_path);
            }
            $data['attachment_path'] = $attachment->store('achievements', 'local');
        }

        $old = $achievement->getAttributes();
        $achievement->update($data);

        app(AuditLogger::class)->log(
            'updated',
            $achievement,
            $old,
            $achievement->getChanges(),
        );

        return $achievement;
    }
}
