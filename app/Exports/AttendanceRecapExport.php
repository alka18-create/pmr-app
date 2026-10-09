<?php

namespace App\Exports;

use App\Services\Reports\AttendanceRecapBuilder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceRecapExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private readonly ?int $eventId = null,
        private readonly ?string $from = null,
        private readonly ?string $to = null,
        private readonly ?int $classId = null,
    ) {}

    public function collection(): Collection
    {
        return collect(
            app(AttendanceRecapBuilder::class)->perMember(
                $this->eventId,
                $this->from,
                $this->to,
                $this->classId,
            )
        );
    }

    public function headings(): array
    {
        return ['Nama', 'Kelas', 'Hadir', 'Izin', 'Sakit', 'Alpa', 'Terlambat', 'Total', 'Persentase Hadir (%)'];
    }

    /**
     * @return array<int, mixed>
     */
    public function map($row): array
    {
        return [
            $row['member'],
            $row['kelas'],
            $row['hadir'],
            $row['izin'],
            $row['sakit'],
            $row['alpa'],
            $row['terlambat'],
            $row['total'],
            $row['persentase'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
