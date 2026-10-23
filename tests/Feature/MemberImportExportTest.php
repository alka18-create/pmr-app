<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Exports\MemberTemplateExport;
use App\Livewire\Members\MemberExport;
use App\Livewire\Members\MemberImport;
use App\Models\AuditLog;
use App\Models\Batch;
use App\Models\Member;
use App\Models\Position;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class MemberImportExportTest extends TestCase
{
    use RefreshDatabase;

    private function pengurus(): User
    {
        return User::factory()->create(['role' => UserRole::Pengurus]);
    }

    private function anggota(): User
    {
        return User::factory()->create(['role' => UserRole::Anggota]);
    }

    private function setupMasters(): array
    {
        $batch = Batch::factory()->create(['name' => 'Angkatan 2026 Impor', 'year' => 2026]);
        $class = SchoolClass::factory()->create(['name' => 'XI IPA Impor', 'level' => 'XI', 'year' => 2026]);
        $position = Position::factory()->create(['name' => 'Anggota Impor', 'level' => 9]);

        return [$batch, $class, $position];
    }

    private function makeXlsx(array $headers, array $rows): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'imp').'.xlsx';

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray(array_merge([$headers], $rows), null, 'A1');
        $writer = new Xlsx($spreadsheet);
        $writer->save($path);

        return UploadedFile::fake()->createWithContent(
            'anggota.xlsx',
            file_get_contents($path),
        );
    }

    private function templateHeaders(): array
    {
        return MemberTemplateExport::headers();
    }

    public function test_anggota_biasa_ditolak_import_export(): void
    {
        $user = $this->anggota();

        $this->actingAs($user)->get(route('members.import'))->assertForbidden();
        $this->actingAs($user)->get(route('members.export'))->assertForbidden();

        Livewire::actingAs($user)
            ->test(MemberImport::class)
            ->assertForbidden();

        Livewire::actingAs($user)
            ->test(MemberExport::class)
            ->assertForbidden();
    }

    public function test_pengurus_dapat_buka_halaman_import_export(): void
    {
        $pengurus = $this->pengurus();

        $this->actingAs($pengurus)->get(route('members.import'))->assertOk()->assertSee('Import Anggota');
        $this->actingAs($pengurus)->get(route('members.export'))->assertOk()->assertSee('Export Anggota');
    }

    public function test_import_baris_valid_masuk_dan_error_dilaporkan(): void
    {
        [$batch, $class, $position] = $this->setupMasters();
        $pengurus = $this->pengurus();

        $headers = $this->templateHeaders();
        $file = $this->makeXlsx($headers, [
            [
                'Budi Import', 'NIS-IMP-1', 'NISN-IMP-1', 'laki',
                '2008-05-01', 'Jakarta', 'Jl. Satu', '0811111111',
                'budi@x.test', 'Ayah Budi', '0822222222', 'O',
                $batch->name, $class->name, $position->name, 'aktif',
                '2026-01-10', null,
            ],
            [
                '', '', '', 'laki',
                '', '', '', '',
                '', '', '', '',
                $batch->name, $class->name, '', 'aktif',
                '2026-01-10', null,
            ],
        ]);

        Livewire::actingAs($pengurus)
            ->test(MemberImport::class)
            ->set('file', $file)
            ->call('validateFile')
            ->assertHasNoErrors();

        $component = Livewire::actingAs($pengurus)
            ->test(MemberImport::class)
            ->set('file', $this->makeXlsx($headers, [
                [
                    'Budi Import', 'NIS-IMP-1', 'NISN-IMP-1', 'laki',
                    '2008-05-01', 'Jakarta', 'Jl. Satu', '0811111111',
                    'budi@x.test', 'Ayah Budi', '0822222222', 'O',
                    $batch->name, $class->name, $position->name, 'aktif',
                    '2026-01-10', null,
                ],
                [
                    '', '', '', 'laki',
                    '', '', '', '',
                    '', '', '', '',
                    $batch->name, $class->name, '', 'aktif',
                    '2026-01-10', null,
                ],
            ]))
            ->call('validateFile');

        $valid = $component->get('valid');
        $rowErrors = $component->get('rowErrors');

        $this->assertCount(1, $valid);
        $this->assertCount(1, $rowErrors);
        $this->assertStringContainsString('Nama Lengkap', $rowErrors[0]['message']);

        $component->call('confirmImport')->assertRedirect(route('members.index'));

        $this->assertDatabaseHas('members', ['full_name' => 'Budi Import', 'nisn' => 'NISN-IMP-1']);

        $log = AuditLog::where('action', 'import')->latest('id')->first();
        $this->assertNotNull($log);
        $this->assertSame(1, $log->new_values['created'] ?? null);
    }

    public function test_import_nisn_duplikat_ditolak(): void
    {
        [$batch, $class, $position] = $this->setupMasters();
        Member::factory()->create(['nisn' => 'NISN-ADA']);
        $pengurus = $this->pengurus();

        $headers = $this->templateHeaders();
        $file = $this->makeXlsx($headers, [[
            'Sudah Ada', 'NIS-LAIN', 'NISN-ADA', 'perempuan',
            '2008-06-01', 'Bandung', 'Jl. Dua', '0812222222',
            null, null, null, null,
            $batch->name, $class->name, $position->name, 'aktif',
            '2026-01-10', null,
        ]]);

        Livewire::actingAs($pengurus)
            ->test(MemberImport::class)
            ->set('file', $file)
            ->call('validateFile');

        $component = Livewire::actingAs($pengurus)
            ->test(MemberImport::class)
            ->set('file', $this->makeXlsx($headers, [[
                'Sudah Ada', 'NIS-LAIN', 'NISN-ADA', 'perempuan',
                '2008-06-01', 'Bandung', 'Jl. Dua', '0812222222',
                null, null, null, null,
                $batch->name, $class->name, $position->name, 'aktif',
                '2026-01-10', null,
            ]]))
            ->call('validateFile');

        $this->assertCount(0, $component->get('valid'));
        $this->assertCount(1, $component->get('rowErrors'));

        $component->call('confirmImport')->assertSessionHas('error');
        $this->assertDatabaseMissing('members', ['full_name' => 'Sudah Ada']);
    }

    public function test_import_kelas_tidak_ada_dilaporkan(): void
    {
        $batch = Batch::factory()->create(['name' => 'Angkatan Cari', 'year' => 2026]);
        SchoolClass::factory()->create(['name' => 'Kelas Asli', 'level' => 'X', 'year' => 2026]);
        $pengurus = $this->pengurus();

        $headers = $this->templateHeaders();
        $file = $this->makeXlsx($headers, [[
            'Orang Baru', 'NIS-BARU-1', 'NISN-BARU-1', 'laki',
            '2008-01-01', 'Depok', null, null,
            null, null, null, null,
            $batch->name, 'Kelas Tidak Ada', null, 'aktif',
            '2026-01-10', null,
        ]]);

        $component = Livewire::actingAs($pengurus)
            ->test(MemberImport::class)
            ->set('file', $file)
            ->call('validateFile');

        $this->assertCount(1, $component->get('valid'));
        $component->call('confirmImport')->assertSessionHas('error');
        $this->assertDatabaseMissing('members', ['full_name' => 'Orang Baru']);
    }

    public function test_export_menghasilkan_audit_log_dan_file(): void
    {
        Member::factory()->count(2)->create();
        $pengurus = $this->pengurus();

        Excel::fake();

        Livewire::actingAs($pengurus)
            ->test(MemberExport::class)
            ->call('download')
            ->assertFileDownloaded();

        Excel::assertDownloaded('anggota.xlsx');

        $log = AuditLog::where('action', 'export')
            ->where('user_id', $pengurus->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertSame('members', $log->new_values['resource'] ?? null);
    }

    public function test_import_mendukung_format_gender_dan_status_fleksibel(): void
    {
        [$batch, $class, $position] = $this->setupMasters();
        $pengurus = $this->pengurus();

        $headers = $this->templateHeaders();
        $file = $this->makeXlsx($headers, [
            [
                'Siti Fleksibel', 'NIS-FLX-1', 'NISN-FLX-1', 'Laki-laki',
                '15/05/2008', 'Jakarta', null, null,
                null, null, null, 'b',
                $batch->name, $class->name, null, 'Aktif',
                '10/01/2026', null,
            ],
            [
                'Rina Fleksibel', 'NIS-FLX-2', 'NISN-FLX-2', 'P',
                '2008-06-20', 'Bandung', null, null,
                null, null, null, null,
                $batch->name, $class->name, null, '',
                '', null,
            ],
        ]);

        $component = Livewire::actingAs($pengurus)
            ->test(MemberImport::class)
            ->set('file', $file)
            ->call('validateFile');

        $this->assertCount(2, $component->get('valid'));
        $this->assertCount(0, $component->get('rowErrors'));

        $component->call('confirmImport')->assertRedirect(route('members.index'));

        $this->assertDatabaseHas('members', [
            'full_name' => 'Siti Fleksibel',
            'gender' => 'laki',
            'status' => 'aktif',
            'blood_type' => 'B',
            'birth_date' => '2008-05-15 00:00:00',
        ]);

        $this->assertDatabaseHas('members', [
            'full_name' => 'Rina Fleksibel',
            'gender' => 'perempuan',
            'status' => 'aktif',
        ]);
    }

    public function test_download_template_dapat_direquest(): void
    {
        $pengurus = $this->pengurus();
        Excel::fake();

        Livewire::actingAs($pengurus)
            ->test(MemberImport::class)
            ->call('downloadTemplate');

        Excel::assertDownloaded('template-import-anggota.xlsx');
    }
}
