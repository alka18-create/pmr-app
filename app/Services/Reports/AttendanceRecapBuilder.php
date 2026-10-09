<?php

namespace App\Services\Reports;

use App\Enums\AttendanceStatus;
use App\Models\AttendanceRecord;
use App\Models\Event;
use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Membangun rekap absensi (docs/02 FR-RPT-01).
 * Logika bisnis agregasi — dipakai Livewire & export (AGENTS.md rule 4).
 */
class AttendanceRecapBuilder
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function perEvent(
        ?int $eventId = null,
        ?string $from = null,
        ?string $to = null,
        ?int $classId = null,
    ): array {
        return $this->baseQuery($eventId, $from, $to, $classId)
            ->with(['member:id,full_name,class_id', 'member.schoolClass:id,name', 'event:id,title,starts_at'])
            ->orderByDesc('checked_in_at')
            ->get()
            ->map(fn (AttendanceRecord $record) => [
                'member' => $record->member?->full_name ?? '—',
                'kelas' => $record->member?->schoolClass?->name ?? '—',
                'event' => $record->event?->title ?? '—',
                'tanggal' => $record->event?->starts_at?->format('d M Y') ?? '—',
                'status' => $record->status->value,
            ])
            ->all();
    }

    /**
     * Ringkasan jumlah per status (hadir/izin/sakit/alpa/terlambat).
     *
     * @return array<string, int>
     */
    public function summaryCounts(
        ?int $eventId = null,
        ?string $from = null,
        ?string $to = null,
        ?int $classId = null,
    ): array {
        $counts = $this->baseQuery($eventId, $from, $to, $classId)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        $summary = [];
        foreach (AttendanceStatus::cases() as $status) {
            $summary[$status->value] = (int) ($counts[$status->value] ?? 0);
        }

        return $summary;
    }

    /**
     * Rekap per anggota: total hadir/izin/sakit/alpa + persentase kehadiran.
     *
     * @return array<int, array<string, mixed>>
     */
    public function perMember(
        ?int $eventId = null,
        ?string $from = null,
        ?string $to = null,
        ?int $classId = null,
    ): array {
        $records = $this->baseQuery($eventId, $from, $to, $classId)
            ->with(['member:id,full_name,class_id', 'member.schoolClass:id,name'])
            ->get()
            ->groupBy('member_id');

        return $records->map(function (Collection $group) {
            $member = $group->first()->member;
            $counts = $group->countBy(fn (AttendanceRecord $r) => $r->status->value);
            $total = $group->count();
            $hadir = (int) ($counts['hadir'] ?? 0);

            return [
                'member' => $member?->full_name ?? '—',
                'kelas' => $member?->schoolClass?->name ?? '—',
                'hadir' => $hadir,
                'izin' => (int) ($counts['izin'] ?? 0),
                'sakit' => (int) ($counts['sakit'] ?? 0),
                'alpa' => (int) ($counts['alpa'] ?? 0),
                'terlambat' => (int) ($counts['terlambat'] ?? 0),
                'total' => $total,
                'persentase' => $total > 0 ? round($hadir / $total * 100, 1) : 0.0,
            ];
        })->sortBy('member')->values()->all();
    }

    /**
     * @return array{event: ?string, periode: string, kelas: ?string, summary: array<string, int>, rows: array<int, array<string, mixed>>}
     */
    public function build(
        ?int $eventId = null,
        ?string $from = null,
        ?string $to = null,
        ?int $classId = null,
    ): array {
        return [
            'event' => $eventId ? Event::whereKey($eventId)->value('title') : null,
            'periode' => $this->periodLabel($from, $to),
            'kelas' => $classId ? SchoolClass::whereKey($classId)->value('name') : null,
            'summary' => $this->summaryCounts($eventId, $from, $to, $classId),
            'rows' => $this->perMember($eventId, $from, $to, $classId),
        ];
    }

    private function baseQuery(
        ?int $eventId,
        ?string $from,
        ?string $to,
        ?int $classId,
    ): Builder {
        return AttendanceRecord::query()
            ->when($eventId, fn (Builder $q) => $q->where('event_id', $eventId))
            ->when($from, fn (Builder $q) => $q->where('checked_in_at', '>=', $from.' 00:00:00'))
            ->when($to, fn (Builder $q) => $q->where('checked_in_at', '<=', $to.' 23:59:59'))
            ->when($classId, fn (Builder $q) => $q->whereHas('member', fn (Builder $m) => $m->where('class_id', $classId)));
    }

    private function periodLabel(?string $from, ?string $to): string
    {
        if (! $from && ! $to) {
            return 'Semua periode';
        }

        $start = $from ? Carbon::parse($from)->format('d M Y') : 'awal';
        $end = $to ? Carbon::parse($to)->format('d M Y') : 'sekarang';

        return $start.' – '.$end;
    }
}
