<?php

namespace App\Actions\Settings;

use App\Models\Setting;

class SaveSetting
{
    public function execute(array $data): Setting
    {
        return Setting::set(
            $data['key'],
            $data['value'] ?? '',
            $data['group'],
        );
    }
}
