<?php

namespace App\Livewire\Website;

use App\Models\Member;
use App\Models\Position;
use App\Models\Setting;
use App\Services\OrganizationPrinciples;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.public', ['title' => 'Profil Organisasi'])]
class OrganizationProfile extends Component
{
    public function render()
    {
        $positions = Position::orderBy('level')->get(['id', 'name']);
        $structure = Member::query()
            ->where('status', 'aktif')
            ->whereHas('position')
            ->with('position:id,name')
            ->with('schoolClass:id,name')
            ->get()
            ->groupBy(fn ($m) => $m->position?->id ?? 0)
            ->map(fn ($group) => $group->take(3)->values())
            ->sortKeys()
            ->flatten(1);

        // Dapat diubah admin via Pengaturan → Profil Organisasi (Setting org_principles).
        $principles = OrganizationPrinciples::resolve(Setting::get('org_principles'));

        return view('livewire.website.organization-profile', [
            'history' => Setting::get('org_history', ''),
            'vision' => Setting::get('org_vision', ''),
            'mission' => Setting::get('org_mission', ''),
            'orgName' => Setting::get('org_name', config('app.name')),
            'structure' => $structure,
            'principles' => $principles,
            'contactEmail' => Setting::get('contact_email', ''),
            'contactPhone' => Setting::get('contact_phone', ''),
            'contactAddress' => Setting::get('contact_address', ''),
            'instagram' => Setting::get('social_instagram', ''),
            'youtube' => Setting::get('social_youtube', ''),
            'tiktok' => Setting::get('social_tiktok', ''),
        ]);
    }
}
