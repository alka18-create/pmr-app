<?php

namespace App\Actions\Achievements;

use App\Models\Achievement;
use App\Services\AuditLogger;

class DeleteAchievement
{
    public function execute(Achievement $achievement): void
    {
        $old = $achievement->getAttributes();

        if ($achievement->attachment_path) {
            \Illuminate\Support\Facades\Storage::disk('local')->delete($achievement->attachment_path);
        }

        $achievement->delete();

        app(AuditLogger::class)->log('deleted', $achievement, $old, []);
    }
}
