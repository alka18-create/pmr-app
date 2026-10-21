<?php

namespace App\Actions\Settings;

use App\Models\Setting;

class SaveSetting
{
    public function execute(array $data): Setting
    {
        $existing = Setting::where('key', $data['key'])->first();
        $isNew = ! $existing;
        $old = $existing ? $existing->getAttributes() : [];

        $setting = Setting::set(
            $data['key'],
            $data['value'] ?? '',
            $data['group'],
        );

        app(\App\Services\AuditLogger::class)->log(
            $isNew ? 'created' : 'updated',
            $setting,
            $old,
            $setting->getAttributes(),
        );

        return $setting;
    }
}
