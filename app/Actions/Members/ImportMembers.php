<?php

namespace App\Actions\Members;

use App\Enums\Gender;
use App\Enums\MemberStatus;
use App\Models\Batch;
use App\Models\Member;
use App\Models\Position;
use App\Models\SchoolClass;
use App\Services\AuditLogger;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;

class ImportMembers
{
    /**
     * Parse + validasi per baris tanpa menyimpan.
     * Balik: ['valid' => list<array>, 'errors' => list<array{row:int,message:string}>]
     *
     * @return array{valid: list<array<string, mixed>>, errors: list<array{row: int, message: string}>}
     */
    public function preview(UploadedFile|string $file): array
    {
        $rows = $this->readRows($file);
        $valid = [];
        $errors = [];
        $seenNis = [];
        $seenNisn = [];

        if ($rows === []) {
            $errors[] = [
                'row' => 0,
                'message' => 'File tidak terbaca atau tidak berisi baris data (cek format template).',
            ];

            return ['valid' => $valid, 'errors' => $errors];
        }

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 1;
            $payload = $this->normalize($row);
            $validator = Validator::make($payload, $this->rules(), $this->messages(), $this->attributes());

            $inBatchErrors = [];
            if (! empty($payload['nis'])) {
                if (in_array($payload['nis'], $seenNis, true)) {
                    $inBatchErrors[] = 'NIS "'.$payload['nis'].'" duplikat pada baris sebelumnya di file import ini.';
                } else {
                    $seenNis[] = $payload['nis'];
                }
            }
            if (! empty($payload['nisn'])) {
                if (in_array($payload['nisn'], $seenNisn, true)) {
                    $inBatchErrors[] = 'NISN "'.$payload['nisn'].'" duplikat pada baris sebelumnya di file import ini.';
                } else {
                    $seenNisn[] = $payload['nisn'];
                }
            }

            if ($validator->fails() || $inBatchErrors !== []) {
                $errorMessages = array_merge($validator->errors()->all(), $inBatchErrors);
                $errors[] = [
                    'row' => $rowNumber,
                    'message' => collect($errorMessages)->implode(' '),
                ];

                continue;
            }

            $payload['row'] = $rowNumber;
            $valid[] = $payload;
        }

        return ['valid' => $valid, 'errors' => $errors];
    }

    /**
     * Insert baris valid (create-only; NISN/NIS duplikat sudah ditolak di preview).
     *
     * @param  list<array<string, mixed>>  $rows
     * @return array{created: int, updated: int}
     */
    public function execute(array $rows): array
    {
        return DB::transaction(function () use ($rows) {
            $created = 0;
            $updated = 0;
            $prepared = [];
            $usedSlugs = [];

            foreach ($rows as $payload) {
                unset($payload['row']);

                $payload = $this->resolveLookups($payload);
                $payload = $this->castPayload($payload);
                $this->assertLookupsExist($payload);

                $slug = Member::generateUniqueSlug($payload['full_name'], null, $usedSlugs);
                $usedSlugs[] = $slug;
                $payload['slug'] = $slug;

                $prepared[] = $payload;
            }

            foreach ($prepared as $payload) {
                Member::create($payload);
                $created++;
            }

            app(AuditLogger::class)->log('import', null, [], [
                'created' => $created,
                'updated' => $updated,
                'total' => $created + $updated,
            ]);

            return ['created' => $created, 'updated' => $updated];
        });
    }

    /**
     * Validasi lookups (bisa dari payload preview berbasis nama, atau setelah resolve berbasis id).
     *
     * @param  array<string, mixed>  $payload
     */
    public function assertLookupsExist(array $payload): void
    {
        if (array_key_exists('batch_id', $payload) || array_key_exists('class_id', $payload)) {
            if (empty($payload['batch_id']) || empty($payload['class_id'])) {
                throw new \DomainException('Angkatan atau kelas pada file tidak ditemukan di master.');
            }

            return;
        }

        $batch = Batch::where('name', $payload['angkatan'] ?? '')->first();
        $class = SchoolClass::where('name', $payload['kelas'] ?? '')->first();

        if ($batch === null || $class === null) {
            throw new \DomainException('Angkatan atau kelas pada file tidak ditemukan di master.');
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function readRows(UploadedFile|string $file): array
    {
        $path = $this->materialize($file);

        if ($path === null) {
            return [];
        }

        try {
            $spreadsheet = IOFactory::load($path);
            $sheet = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
            $spreadsheet->disconnectWorksheets();
        } catch (Throwable $e) {
            report($e);

            return [];
        }

        if (count($sheet) < 2) {
            return [];
        }

        $headers = array_map(
            fn ($h) => Str::of((string) $h)->lower()->replace([' ', '-'], '_')->toString(),
            array_shift($sheet),
        );

        $rows = [];
        foreach ($sheet as $line) {
            if (trim(implode('', array_map(fn ($v) => is_scalar($v) ? (string) $v : '', $line))) === '') {
                continue;
            }
            $rows[] = array_combine($headers, array_pad($line, count($headers), null));
        }

        return $rows;
    }

    /**
     * Pastikan path readable; fallback copy isi stream Livewire ke file sementara.
     */
    private function materialize(UploadedFile|string $file): ?string
    {
        if (is_string($file)) {
            return $file !== '' && is_readable($file) ? $file : null;
        }

        $real = $file->getRealPath() ?: $file->getPathname();

        if (is_string($real) && $real !== '' && is_readable($real)) {
            clearstatcache(true, $real);
            if (@filesize($real) > 0) {
                return $real;
            }
        }

        $contents = null;

        if ($file instanceof TemporaryUploadedFile) {
            try {
                $contents = $file->get();
            } catch (Throwable) {
                $contents = null;
            }
        }

        if (! is_string($contents) || $contents === '') {
            $contents = is_string($real) && $real !== '' ? @file_get_contents($real) : false;
        }

        if (! is_string($contents) || $contents === '') {
            return null;
        }

        $tmp = tempnam(sys_get_temp_dir(), 'imp').'.xlsx';
        if ($tmp === false || file_put_contents($tmp, $contents) === false) {
            return null;
        }

        return $tmp;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function normalize(array $row): array
    {
        $map = [
            'nama_lengkap' => 'full_name',
            'nama' => 'full_name',
            'jenis_kelamin' => 'gender',
            'tanggal_lahir' => 'birth_date',
            'tempat_lahir' => 'birth_place',
            'no_hp' => 'phone',
            'email' => 'email_pribadi',
            'nama_ortu' => 'parent_name',
            'no_hp_ortu' => 'parent_phone',
            'gol_darah' => 'blood_type',
            'bergabung_sejak' => 'joined_at',
        ];

        $payload = [];
        foreach ($row as $key => $value) {
            $key = $map[$key] ?? $key;
            $payload[$key] = is_string($value) ? trim($value) : $value;
        }

        foreach (['nis', 'nisn', 'phone', 'parent_phone', 'blood_type', 'address', 'notes', 'birth_place', 'email_pribadi', 'parent_name'] as $nullable) {
            if (($payload[$nullable] ?? null) === '') {
                $payload[$nullable] = null;
            }
        }

        // Normalisasi jenis kelamin (toleran terhadap Laki, Laki-laki, L, Perempuan, P, dsb.)
        if (isset($payload['gender']) && is_string($payload['gender'])) {
            $g = Str::of($payload['gender'])->trim()->lower()->toString();
            if (in_array($g, ['laki', 'laki-laki', 'l', 'pria', 'male', 'm'])) {
                $payload['gender'] = 'laki';
            } elseif (in_array($g, ['perempuan', 'p', 'wanita', 'female', 'f'])) {
                $payload['gender'] = 'perempuan';
            }
        }

        // Normalisasi status anggota (default ke 'aktif' bila tidak diisi)
        if (! isset($payload['status']) || $payload['status'] === null || $payload['status'] === '') {
            $payload['status'] = 'aktif';
        } elseif (is_string($payload['status'])) {
            $s = Str::of($payload['status'])->trim()->lower()->toString();
            if (in_array($s, ['aktif', 'active'])) {
                $payload['status'] = 'aktif';
            } elseif (in_array($s, ['alumni'])) {
                $payload['status'] = 'alumni';
            } elseif (in_array($s, ['nonaktif', 'non-aktif', 'tidak aktif', 'inactive'])) {
                $payload['status'] = 'nonaktif';
            }
        }

        // Normalisasi golongan darah
        if (isset($payload['blood_type']) && is_string($payload['blood_type'])) {
            $bt = Str::of($payload['blood_type'])->trim()->upper()->toString();
            $payload['blood_type'] = in_array($bt, ['A', 'B', 'AB', 'O']) ? $bt : null;
        }

        // Normalisasi format tanggal umum (DD/MM/YYYY atau DD-MM-YYYY -> YYYY-MM-DD)
        foreach (['birth_date', 'joined_at'] as $dateKey) {
            if (isset($payload[$dateKey]) && is_string($payload[$dateKey]) && $payload[$dateKey] !== '') {
                $dateStr = trim($payload[$dateKey]);
                if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $dateStr, $matches)) {
                    $payload[$dateKey] = sprintf('%04d-%02d-%02d', (int) $matches[3], (int) $matches[2], (int) $matches[1]);
                }
            }
        }

        // Default tanggal bergabung ke hari ini bila tidak diisi di template
        if (! isset($payload['joined_at']) || $payload['joined_at'] === null || $payload['joined_at'] === '') {
            $payload['joined_at'] = now()->toDateString();
        }

        return $payload;
    }

    /**
     * @return array<string, string>
     */
    private function messages(): array
    {
        return [
            'full_name.required' => 'Nama Lengkap wajib diisi.',
            'gender.required' => 'Jenis kelamin wajib diisi.',
            'gender.enum' => 'Jenis kelamin tidak valid. Gunakan "laki" (atau Laki-laki / L) atau "perempuan" (atau P).',
            'status.required' => 'Status wajib diisi.',
            'status.enum' => 'Status tidak valid. Gunakan "aktif", "alumni", atau "nonaktif".',
            'angkatan.required' => 'Angkatan wajib diisi (sesuai nama di menu Master Data).',
            'kelas.required' => 'Kelas wajib diisi (sesuai nama di menu Master Data).',
            'joined_at.required' => 'Tanggal bergabung wajib diisi.',
            'joined_at.date' => 'Format tanggal bergabung tidak valid (contoh: YYYY-MM-DD atau DD/MM/YYYY).',
            'birth_date.date' => 'Format tanggal lahir tidak valid (contoh: YYYY-MM-DD atau DD/MM/YYYY).',
            'birth_date.before' => 'Tanggal lahir harus sebelum hari ini.',
            'nis.unique' => 'NIS sudah digunakan anggota lain.',
            'nisn.unique' => 'NISN sudah digunakan anggota lain.',
        ];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:100'],
            'gender' => ['required', Rule::enum(Gender::class)],
            'nis' => ['nullable', 'string', 'max:20', 'unique:members,nis'],
            'nisn' => ['nullable', 'string', 'max:20', 'unique:members,nisn'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:2000'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email_pribadi' => ['nullable', 'email', 'max:255'],
            'parent_name' => ['nullable', 'string', 'max:100'],
            'parent_phone' => ['nullable', 'string', 'max:20'],
            'blood_type' => ['nullable', Rule::in(['A', 'B', 'AB', 'O'])],
            'angkatan' => ['required', 'string'],
            'kelas' => ['required', 'string'],
            'jabatan' => ['nullable', 'string'],
            'status' => ['required', Rule::enum(MemberStatus::class)],
            'joined_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function attributes(): array
    {
        return [
            'full_name' => 'Nama Lengkap',
            'gender' => 'Jenis Kelamin',
            'nis' => 'NIS',
            'nisn' => 'NISN',
            'birth_date' => 'Tanggal Lahir',
            'birth_place' => 'Tempat Lahir',
            'address' => 'Alamat',
            'phone' => 'No. HP',
            'email_pribadi' => 'Email Pribadi',
            'parent_name' => 'Nama Orang Tua',
            'parent_phone' => 'No. HP Orang Tua',
            'blood_type' => 'Gol. Darah',
            'angkatan' => 'Angkatan',
            'kelas' => 'Kelas',
            'jabatan' => 'Jabatan',
            'status' => 'Status',
            'joined_at' => 'Bergabung Sejak',
            'notes' => 'Catatan',
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function resolveLookups(array $payload): array
    {
        $batch = Batch::where('name', $payload['angkatan'] ?? '')->first();
        $class = SchoolClass::where('name', $payload['kelas'] ?? '')->first();
        $position = isset($payload['jabatan']) && $payload['jabatan'] !== ''
            ? Position::where('name', $payload['jabatan'])->first()
            : null;

        unset($payload['angkatan'], $payload['kelas'], $payload['jabatan']);

        $payload['batch_id'] = $batch?->id;
        $payload['class_id'] = $class?->id;
        $payload['position_id'] = $position?->id;

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function castPayload(array $payload): array
    {
        if (isset($payload['gender']) && is_string($payload['gender'])) {
            $payload['gender'] = Gender::from(Str::lower($payload['gender']));
        }
        if (isset($payload['status']) && is_string($payload['status'])) {
            $payload['status'] = MemberStatus::from(Str::lower($payload['status']));
        }

        return $payload;
    }
}
