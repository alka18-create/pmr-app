<?php

namespace App\Services\Reports;

use App\Enums\MemberStatus;
use App\Models\Batch;
use App\Models\Member;
use App\Models\Position;
use App\Models\SchoolClass;

/**
 * Statistik anggota (docs/02 FR-RPT-02).
 */
class MemberStatistics
{
    public function total(): int
    {
        return Member::query()->count();
    }

    /**
     * @return array<int, array{label: string, value: int}>
     */
    public function perClass(): array
    {
        return SchoolClass::query()
            ->withCount('members')
            ->orderBy('name')
            ->get()
            ->map(fn (SchoolClass $class) => [
                'label' => $class->name,
                'value' => (int) $class->members_count,
            ])
            ->all();
    }

    /**
     * @return array<int, array{label: string, value: int}>
     */
    public function perBatch(): array
    {
        return Batch::query()
            ->withCount('members')
            ->orderByDesc('year')
            ->get()
            ->map(fn (Batch $batch) => [
                'label' => $batch->name,
                'value' => (int) $batch->members_count,
            ])
            ->all();
    }

    /**
     * @return array<int, array{label: string, value: int}>
     */
    public function perStatus(): array
    {
        $counts = Member::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        return collect(MemberStatus::cases())
            ->map(fn (MemberStatus $status) => [
                'label' => $status->value,
                'value' => (int) ($counts[$status->value] ?? 0),
            ])
            ->all();
    }

    /**
     * @return array<int, array{label: string, value: int}>
     */
    public function perPosition(): array
    {
        $counts = Member::query()
            ->selectRaw('position_id, COUNT(*) as total')
            ->groupBy('position_id')
            ->pluck('total', 'position_id')
            ->all();

        return Position::query()
            ->orderBy('level')
            ->orderBy('name')
            ->get()
            ->map(fn (Position $position) => [
                'label' => $position->name,
                'value' => (int) ($counts[$position->id] ?? 0),
            ])
            ->all();
    }

    /**
     * @return array{total: int, per_class: array, per_batch: array, per_status: array, per_position: array}
     */
    public function build(): array
    {
        return [
            'total' => $this->total(),
            'per_class' => $this->perClass(),
            'per_batch' => $this->perBatch(),
            'per_status' => $this->perStatus(),
            'per_position' => $this->perPosition(),
        ];
    }
}
