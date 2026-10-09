<?php

namespace App\Services\Reports;

use App\Enums\AttendanceStatus;
use App\Enums\EventType;
use App\Models\AttendanceRecord;
use App\Models\Event;
use App\Models\EventParticipant;

/**
 * Statistik kegiatan (docs/02 FR-RPT-02).
 */
class EventStatistics
{
    public function total(): int
    {
        return Event::query()->count();
    }

    /**
     * Jumlah kegiatan per bulan untuk tahun tertentu.
     *
     * @return array<int, array{label: string, value: int}>
     */
    public function perMonth(?int $year = null): array
    {
        $year ??= (int) now()->year;

        $counts = Event::query()
            ->whereYear('starts_at', $year)
            ->get(['starts_at'])
            ->countBy(fn (Event $event) => (int) $event->starts_at?->month)
            ->all();

        $months = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
            7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        $result = [];
        foreach ($months as $num => $name) {
            $result[] = [
                'label' => $name,
                'value' => (int) ($counts[$num] ?? 0),
            ];
        }

        return $result;
    }

    /**
     * Jumlah kegiatan per jenis.
     *
     * @return array<int, array{label: string, value: int}>
     */
    public function perType(): array
    {
        $counts = Event::query()
            ->selectRaw('type, COUNT(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type')
            ->all();

        return collect(EventType::cases())
            ->map(fn (EventType $type) => [
                'label' => $type->value,
                'value' => (int) ($counts[$type->value] ?? 0),
            ])
            ->all();
    }

    public function totalParticipants(): int
    {
        return EventParticipant::query()->count();
    }

    public function totalAttendance(): int
    {
        return AttendanceRecord::query()->count();
    }

    /**
     * Rasio kehadiran = hadir / total catatan absensi (persen 0–100).
     */
    public function attendanceRatio(): float
    {
        $total = $this->totalAttendance();

        if ($total === 0) {
            return 0.0;
        }

        $hadir = AttendanceRecord::query()
            ->where('status', AttendanceStatus::Hadir->value)
            ->count();

        return round($hadir / $total * 100, 1);
    }

    /**
     * @return array{total: int, total_participants: int, total_attendance: int, attendance_ratio: float, per_month: array, per_type: array}
     */
    public function build(?int $year = null): array
    {
        return [
            'total' => $this->total(),
            'total_participants' => $this->totalParticipants(),
            'total_attendance' => $this->totalAttendance(),
            'attendance_ratio' => $this->attendanceRatio(),
            'per_month' => $this->perMonth($year),
            'per_type' => $this->perType(),
        ];
    }
}
