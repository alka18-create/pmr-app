<?php

namespace App\Livewire\Members;

use App\Actions\Members\MarkBatchAsAlumni;
use App\Actions\Members\SetMemberStatus;
use App\Enums\MemberStatus;
use App\Models\Batch;
use App\Models\Member;
use App\Models\SchoolClass;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.panel')]
class MemberIndex extends Component
{
    use AuthorizesRequests, WithPagination;

    public string $search = '';

    public string $classFilter = '';

    public string $batchFilter = '';

    public string $statusFilter = '';

    public function mount(): void
    {
        $this->authorize('create', Member::class);
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'classFilter', 'batchFilter', 'statusFilter'], true)) {
            $this->resetPage();
        }
    }

    public function toggleStatus(Member $member, SetMemberStatus $setStatus)
    {
        $this->authorize('update', $member);

        $newStatus = $member->status === MemberStatus::Aktif
            ? MemberStatus::Nonaktif
            : MemberStatus::Aktif;

        $setStatus->execute($member, $newStatus);

        session()->flash('success', sprintf(
            'Status %s diubah menjadi %s.',
            $member->full_name,
            $newStatus->value,
        ));

        return redirect()->route('members.index');
    }

    /**
     * Bulk tandai alumni untuk angkatan terpilih (docs/13 §7.3).
     */
    public function markBatchAsAlumni(MarkBatchAsAlumni $markAlumni)
    {
        $this->authorize('create', Member::class);

        if ($this->batchFilter === '') {
            session()->flash('error', 'Pilih angkatan terlebih dahulu (filter Angkatan).');

            return redirect()->route('members.index');
        }

        $batch = Batch::findOrFail($this->batchFilter);
        $count = $markAlumni->execute($batch);

        session()->flash('success', sprintf(
            '%d anggota angkatan %s ditandai sebagai alumni.',
            $count,
            $batch->name,
        ));

        return redirect()->route('members.index');
    }

    public function render()
    {
        $members = Member::query()
            ->with(['batch', 'schoolClass', 'position'])
            ->when($this->search !== '', function ($query) {
                $term = '%'.mb_strtolower($this->search).'%';
                $query->where(function ($q) use ($term) {
                    $q->whereRaw('LOWER(full_name) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(nis) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(nisn) LIKE ?', [$term]);
                });
            })
            ->when($this->classFilter !== '', fn ($query) => $query->where('class_id', $this->classFilter))
            ->when($this->batchFilter !== '', fn ($query) => $query->where('batch_id', $this->batchFilter))
            ->when($this->statusFilter !== '', fn ($query) => $query->where('status', $this->statusFilter))
            ->orderBy('full_name')
            ->paginate(10);

        return view('livewire.members.member-index', [
            'members' => $members,
            'classes' => SchoolClass::orderBy('name')->get(),
            'batches' => Batch::orderByDesc('year')->get(),
            'statuses' => MemberStatus::cases(),
        ]);
    }
}
