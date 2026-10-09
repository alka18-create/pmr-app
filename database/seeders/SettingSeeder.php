<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Services\OrganizationPrinciples;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        Setting::set('org_name', 'PMR Wira SMA Negeri 1', 'general');
        Setting::set('org_history', 'PMR Wira SMA Negeri 1 didirikan untuk membina siswa dalam kegiatan Palang Merah Remaja.', 'general');
        Setting::set('org_vision', 'Menjadi organisasi kesiswaan yang unggul dalam bidang kesehatan dan kebencanaan.', 'general');
        Setting::set('org_mission', 'Meningkatkan keterampilan pertolongan pertama, menumbuhkan kepedulian sosial, dan membangun kedisiplinan anggota.', 'general');

        Setting::set('contact_email', 'pmr@sman1.sch.id', 'contact');
        Setting::set('contact_phone', '021-1234567', 'contact');
        Setting::set('contact_address', 'Jl. Pendidikan No. 1, Jakarta', 'contact');

        Setting::set('social_instagram', 'https://instagram.com/pmr.wira', 'contact');
        Setting::set('social_youtube', 'https://youtube.com/@pmrwira', 'contact');
        Setting::set('social_tiktok', 'https://tiktok.com/@pmrwira', 'contact');

        Setting::set('public_show_alumni', '0', 'feature');
        Setting::set('public_show_nonaktif', '0', 'feature');

        Setting::set('org_principles', json_encode(OrganizationPrinciples::defaults(), JSON_UNESCAPED_UNICODE), 'general');
    }
}
