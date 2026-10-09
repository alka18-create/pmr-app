<?php

namespace App\Actions\Reports;

use App\Exports\AttendanceRecapExport;
use App\Models\Setting;
use App\Services\AuditLogger;
use App\Services\Reports\AttendanceRecapBuilder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;

class ExportAttendanceRecap
{
    public function __construct(
        private readonly AttendanceRecapBuilder $builder,
    ) {}

    public function excel(?int $eventId = null, ?string $from = null, ?string $to = null, ?int $classId = null)
    {
        $this->audit($eventId, $from, $to, $classId, 'excel');

        return Excel::download(
            new AttendanceRecapExport($eventId, $from, $to, $classId),
            'rekap-absensi.xlsx',
        );
    }

    public function pdf(?int $eventId = null, ?string $from = null, ?string $to = null, ?int $classId = null): Response
    {
        $this->audit($eventId, $from, $to, $classId, 'pdf');

        $pdf = Pdf::loadView('reports.attendance-pdf', [
            'recap' => $this->builder->build($eventId, $from, $to, $classId),
            'orgName' => Setting::get('org_name', config('app.name')),
            'schoolName' => Setting::get('org_name', config('app.name')),
            'generatedAt' => now()->translatedFormat('d F Y H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('rekap-absensi.pdf');
    }

    private function audit(?int $eventId, ?string $from, ?string $to, ?int $classId, string $format): void
    {
        app(AuditLogger::class)->log('export', null, [], [
            'resource' => 'attendance_recap',
            'format' => $format,
            'event_id' => $eventId,
            'from' => $from,
            'to' => $to,
            'class_id' => $classId,
        ]);
    }
}
