<?php

namespace App\Actions\Settings;

use App\Models\Setting;
use App\Services\OrganizationPrinciples;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SaveOrganizationProfile
{
    public function execute(array $data, ?UploadedFile $logo = null): void
    {
        if ($logo instanceof UploadedFile && $logo->isValid()) {
            $old = (string) Setting::get('org_logo', '');

            if ($old !== '') {
                Storage::disk('public')->delete($old);
            }

            $data['logo_path'] = $logo->store('settings', 'public');
        }

        $map = [
            'name' => ['org_name', 'general'],
            'history' => ['org_history', 'general'],
            'vision' => ['org_vision', 'general'],
            'mission' => ['org_mission', 'general'],
            'email' => ['contact_email', 'contact'],
            'phone' => ['contact_phone', 'contact'],
            'address' => ['contact_address', 'contact'],
            'instagram' => ['social_instagram', 'contact'],
            'youtube' => ['social_youtube', 'contact'],
            'tiktok' => ['social_tiktok', 'contact'],
        ];

        foreach ($map as $field => [$key, $group]) {
            Setting::set($key, $data[$field] ?? '', $group);
        }

        if (array_key_exists('principles', $data)) {
            $principles = OrganizationPrinciples::resolve($data['principles'] ?? []);
            Setting::set('org_principles', json_encode($principles, JSON_UNESCAPED_UNICODE), 'general');
        }

        if (array_key_exists('logo_path', $data)) {
            Setting::set('org_logo', $data['logo_path'], 'general');
        }

        app(\App\Services\AuditLogger::class)->log(
            'updated',
            null,
            [],
            ['profile' => 'organization_profile', 'fields' => array_keys($data)],
        );
    }
}
