<?php

namespace App\Livewire\Website;

use App\Models\Batch;
use App\Models\Member;
use App\Models\SchoolClass;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.public', ['title' => 'Direktori Anggota'])]
class MemberDirectory extends Component
{
    use WithPagination;

    public string $search = '';

    public string $classFilter = '';

    public string $batchFilter = '';

    public string $statusFilter = '';

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'classFilter', 'batchFilter', 'statusFilter'], true)) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $visibleStatuses = Member::publiclyVisibleStatusValues();

        $statusFilter = in_array($this->statusFilter, $visibleStatuses, true)
            ? $this->statusFilter
            : '';

        $members = Member::query()
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
            ->whereIn('status', $visibleStatuses)
            ->when($this->search !== '', function ($query) {
                $term = '%'.mb_strtolower($this->search).'%';
                $query->whereRaw('LOWER(full_name) LIKE ?', [$term]);
            })
            ->when($this->classFilter !== '', fn ($query) => $query->where('class_id', $this->classFilter))
            ->when($this->batchFilter !== '', fn ($query) => $query->where('batch_id', $this->batchFilter))
            ->when($statusFilter !== '', fn ($query) => $query->where('status', $statusFilter))
            ->orderBy('full_name')
            ->paginate(12)
            ->through(fn (Member $member) => $member->toPublicArray());

        return view('livewire.website.member-directory', [
            'members' => $members,
            'classes' => SchoolClass::query()->select(['id', 'name'])->orderBy('name')->get(),
            'batches' => Batch::query()->select(['id', 'name'])->orderByDesc('year')->get(),
            'visibleStatuses' => $visibleStatuses,
        ]);
    }
}
