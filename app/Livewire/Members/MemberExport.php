<?php

namespace App\Livewire\Members;

use App\Actions\Members\ExportMembers;
use App\Enums\MemberStatus;
use App\Models\Batch;
use App\Models\Member;
use App\Models\SchoolClass;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.panel')]
class MemberExport extends Component
{
    use AuthorizesRequests;

    public string $classFilter = '';

    public string $batchFilter = '';

    public string $statusFilter = '';

    public function mount(): void
    {
        $this->authorize('export', Member::class);
    }

    public function download(ExportMembers $export)
    {
        $this->authorize('export', Member::class);

        return $export->download(
            $this->classFilter !== '' ? (int) $this->classFilter : null,
            $this->batchFilter !== '' ? (int) $this->batchFilter : null,
            $this->statusFilter !== '' ? $this->statusFilter : null,
        );
    }

    public function render()
    {
        return view('livewire.members.member-export', [
            'classes' => SchoolClass::orderBy('name')->get(),
            'batches' => Batch::orderByDesc('year')->get(),
            'statuses' => MemberStatus::cases(),
        ]);
    }
}
