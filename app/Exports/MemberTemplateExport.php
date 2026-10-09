<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MemberTemplateExport implements FromCollection, WithHeadings, WithStyles
{
    public function collection(): Collection
    {
        return collect([]);
    }

    public function headings(): array
    {
        return self::headers();
    }

    /**
     * @return list<string>
     */
    public static function headers(): array
    {
        return [
            'full_name',
            'nis',
            'nisn',
            'gender',
            'birth_date',
            'birth_place',
            'address',
            'phone',
            'email_pribadi',
            'parent_name',
            'parent_phone',
            'blood_type',
            'angkatan',
            'kelas',
            'jabatan',
            'status',
            'joined_at',
            'notes',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
