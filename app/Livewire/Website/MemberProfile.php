<?php

namespace App\Livewire\Website;

use App\Models\Member;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('components.layouts.public', ['title' => 'Profil Anggota'])]
class MemberProfile extends Component
{
    #[Locked]
    public string $slug = '';

    public function mount(string $slug): void
    {
        $this->slug = $slug;
    }

    public function render()
    {
        $member = Member::query()
            ->select([
                'id',
                'full_name',
                'slug',
                'gender',
                'photo_path',
                'status',
                'joined_at',
                'batch_id',
                'class_id',
                'position_id',
            ])
            ->with(['batch:id,name,year', 'schoolClass:id,name,level', 'position:id,name'])
            ->where('slug', $this->slug)
            ->whereIn('status', Member::publiclyVisibleStatusValues())
            ->firstOrFail();

        return view('livewire.website.member-profile', [
            'member' => $member->toPublicArray(),
        ]);
    }
}
