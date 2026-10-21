<?php

namespace App\Actions\Achievements;

use App\Models\Achievement;
use App\Services\AuditLogger;

class CreateAchievement
{
    public function execute(array $data, ?\Illuminate\Http\UploadedFile $attachment = null): Achievement
    {
        if ($attachment) {
            $data['attachment_path'] = $attachment->store('achievements', 'local');
        }

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
