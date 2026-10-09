<?php

namespace App\Livewire\Reports;

use App\Actions\Reports\ExportAttendanceRecap;
use App\Enums\AttendanceStatus;
use App\Models\Event;
use App\Models\SchoolClass;
use App\Services\Reports\AttendanceRecapBuilder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.panel')]
class AttendanceRecap extends Component
{
    use AuthorizesRequests;

    public string $eventFilter = '';

    public string $from = '';

    public string $to = '';

    public string $classFilter = '';

    public function mount(): void
    {
        Gate::authorize('access-pengurus-area');
    }

    public function exportExcel(ExportAttendanceRecap $export)
    {
        Gate::authorize('access-pengurus-area');

        return $export->excel(
            $this->eventFilter !== '' ? (int) $this->eventFilter : null,
            $this->from !== '' ? $this->from : null,
            $this->to !== '' ? $this->to : null,
            $this->classFilter !== '' ? (int) $this->classFilter : null,
        );
    }

    public function pdfUrl(): string
    {
        return route('reports.attendance.pdf', array_filter([
            'event' => $this->eventFilter !== '' ? (int) $this->eventFilter : null,
            'from' => $this->from !== '' ? $this->from : null,
            'to' => $this->to !== '' ? $this->to : null,
            'class' => $this->classFilter !== '' ? (int) $this->classFilter : null,
        ], fn ($value) => $value !== null && $value !== ''));
    }

    public function resetFilters(): void
    {
        $this->reset(['eventFilter', 'from', 'to', 'classFilter']);
    }

    public function render(AttendanceRecapBuilder $builder)
    {
        $eventId = $this->eventFilter !== '' ? (int) $this->eventFilter : null;
        $classId = $this->classFilter !== '' ? (int) $this->classFilter : null;
        $from = $this->from !== '' ? $this->from : null;
        $to = $this->to !== '' ? $this->to : null;

        return view('livewire.reports.attendance-recap', [
            'summary' => $builder->summaryCounts($eventId, $from, $to, $classId),
            'rows' => $builder->perMember($eventId, $from, $to, $classId),
            'events' => Event::orderByDesc('starts_at')->limit(100)->get(['id', 'title']),
            'classes' => SchoolClass::orderBy('name')->get(['id', 'name']),
            'statuses' => AttendanceStatus::cases(),
        ]);
    }
}
