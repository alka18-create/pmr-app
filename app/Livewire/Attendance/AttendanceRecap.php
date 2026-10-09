<?php

namespace App\Livewire\Attendance;

use App\Enums\AttendanceStatus;
use App\Models\AttendanceRecord;
use App\Models\Member;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.panel')]
class AttendanceRecap extends Component
{
    use AuthorizesRequests, WithPagination;

    public string $memberFilter = '';

    public string $statusFilter = '';

    public function mount(): void
    {
        Gate::authorize('access-pengurus-area');
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['memberFilter', 'statusFilter'], true)) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $records = AttendanceRecord::query()
            ->with(['event:id,title,starts_at,status', 'member:id,full_name,slug,class_id', 'member.schoolClass:id,name'])
            ->when($this->memberFilter !== '', fn ($query) => $query->where('member_id', $this->memberFilter))
            ->when($this->statusFilter !== '', fn ($query) => $query->where('status', $this->statusFilter))
            ->orderByDesc('checked_in_at')
            ->paginate(15);

        $members = Member::query()
            ->select(['id', 'full_name'])
            ->orderBy('full_name')
            ->get();

        return view('livewire.attendance.attendance-recap', [
            'records' => $records,
            'members' => $members,
            'statuses' => AttendanceStatus::cases(),
        ]);
    }
}
