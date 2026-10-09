<?php

namespace App\Livewire\Reports;

use App\Actions\Reports\ExportAttendanceRecap;
use App\Services\Reports\AttendanceRecapBuilder;
use App\Services\Reports\EventStatistics;
use App\Services\Reports\MemberStatistics;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.panel')]
class ReportIndex extends Component
{
    public string $reportType = 'absensi';

    public bool $generated = false;

    public function mount(): void
    {
        Gate::authorize('access-pengurus-area');
    }

    public function generate(): void
    {
        Gate::authorize('access-pengurus-area');

        $this->validate([
            'reportType' => ['required', 'in:absensi,anggota,kegiatan'],
        ]);

        $this->generated = true;
    }

    public function exportExcel(ExportAttendanceRecap $export)
    {
        Gate::authorize('access-pengurus-area');

        abort_unless($this->reportType === 'absensi', 400, 'Export Excel hanya untuk laporan absensi.');

        return $export->excel();
    }

    public function render(
        AttendanceRecapBuilder $attendance,
        MemberStatistics $members,
        EventStatistics $events,
    ) {
        return view('livewire.reports.report-index', [
            'attendanceSummary' => $this->generated && $this->reportType === 'absensi'
                ? $attendance->summaryCounts()
                : null,
            'attendanceRows' => $this->generated && $this->reportType === 'absensi'
                ? $attendance->perMember()
                : [],
            'memberStats' => $this->generated && $this->reportType === 'anggota'
                ? $members->build()
                : null,
            'eventStats' => $this->generated && $this->reportType === 'kegiatan'
                ? $events->build()
                : null,
        ]);
    }
}
