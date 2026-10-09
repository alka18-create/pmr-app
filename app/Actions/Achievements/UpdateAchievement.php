<?php

namespace App\Actions\Achievements;

use App\Models\Achievement;
use App\Services\AuditLogger;

class UpdateAchievement
{
    public function execute(Achievement $achievement, array $data): Achievement
    {
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
