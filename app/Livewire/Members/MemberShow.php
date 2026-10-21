<?php

namespace App\Livewire\Members;

use App\Actions\Members\PurgeMemberPersonalData;
use App\Models\Member;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('components.layouts.panel')]
class MemberShow extends Component
{
    use AuthorizesRequests;

    #[Locked]
    public Member $member;

    #[Locked]
    public bool $showInternal = false;

    /** @var array<string, mixed>|null */
    #[Locked]
    public ?array $publicData = null;

    public function mount(Member $member): void
    {
        $member->load(['batch', 'schoolClass', 'position', 'user']);

        $this->member = $member;

        if ($member->isPublicProfile() && ! auth()->user()) {
            $this->publicData = $member->toPublicArray();
            $this->showInternal = false;

            return;
        }

        // Data internal hanya untuk pengurus/admin (docs/05 §6)
        $this->authorize('viewInternal', $member);
        $this->showInternal = true;
        $this->publicData = $member->toPublicArray();
    }

    /**
     * Prosedur penghapusan data pribadi (permintaan orang tua/sekolah) — docs/08 §3.3.
     */
    public function purgePersonalData(PurgeMemberPersonalData $purge)
    {
        $this->authorize('delete', $this->member);

        $purge->execute($this->member);

        session()->flash('success', 'Data pribadi anggota telah dihapus (dianonimkan).');

        return redirect()->route('members.index');
    }

    public function render()
    {
        return view('livewire.members.member-show');
    }
}
