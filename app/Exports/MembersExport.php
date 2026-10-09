<?php

namespace App\Exports;

use App\Models\Member;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MembersExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private readonly ?int $classId = null,
        private readonly ?int $batchId = null,
        private readonly ?string $status = null,
    ) {}

    public function collection(): Collection
    {
        return Member::query()
            ->with(['batch', 'schoolClass', 'position'])
            ->when($this->classId, fn ($q) => $q->where('class_id', $this->classId))
            ->when($this->batchId, fn ($q) => $q->where('batch_id', $this->batchId))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->orderBy('full_name')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Nama Lengkap',
            'NIS',
            'NISN',
            'Jenis Kelamin',
            'Tanggal Lahir',
            'Tempat Lahir',
            'Alamat',
            'No. HP',
            'Email Pribadi',
            'Nama Orang Tua',
            'No. HP Orang Tua',
            'Gol. Darah',
            'Angkatan',
            'Kelas',
            'Jabatan',
            'Status',
            'Bergabung Sejak',
            'Catatan',
        ];
    }

    /**
     * @return array<int, mixed>
     */
    public function map($member): array
    {
        return [
            $member->full_name,
            $member->nis,
            $member->nisn,
            $member->gender?->value,
            $member->birth_date?->toDateString(),
            $member->birth_place,
            $member->address,
            $member->phone,
            $member->email_pribadi,
            $member->parent_name,
            $member->parent_phone,
            $member->blood_type,
            $member->batch?->name,
            $member->schoolClass?->name,
            $member->position?->name,
            $member->status?->value,
            $member->joined_at?->toDateString(),
            $member->notes,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
