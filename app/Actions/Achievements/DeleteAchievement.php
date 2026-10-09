<?php

namespace App\Actions\Achievements;

use App\Models\Achievement;
use App\Services\AuditLogger;

class DeleteAchievement
{
    public function execute(Achievement $achievement): void
    {
        $old = $achievement->getAttributes();
        $achievement->delete();

        app(AuditLogger::class)->log('deleted', $achievement, $old, []);
    }
}
