<?php

namespace App\Actions\Settings;

use App\Models\Setting;
use App\Services\AuditLogger;

class DeleteSetting
{
    public function execute(Setting $setting): void
    {
        $old = $setting->getAttributes();
        $setting->delete();

        app(AuditLogger::class)->log('deleted', $setting, $old, []);
    }
}
