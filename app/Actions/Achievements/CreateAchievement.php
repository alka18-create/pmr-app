<?php

namespace App\Actions\Achievements;

use App\Models\Achievement;
use App\Services\AuditLogger;

class CreateAchievement
{
    public function execute(array $data): Achievement
    {
        $achievement = Achievement::create($data);

        app(AuditLogger::class)->log(
            'created',
            $achievement,
            [],
            $achievement->getAttributes(),
        );

        return $achievement;
    }
}
