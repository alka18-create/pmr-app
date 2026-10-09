<?php

namespace Tests\Feature;

use App\Enums\AttendanceMethod;
use App\Enums\AttendanceStatus;
use App\Enums\EventType;
use App\Enums\MemberStatus;
use App\Enums\UserRole;
use App\Livewire\Reports\AttendanceRecap as AttendanceReport;
use App\Livewire\Reports\ReportIndex;
use App\Livewire\Stats\EventStats;
use App\Livewire\Stats\MemberStats;
use App\Models\AttendanceRecord;
use App\Models\AuditLog;
use App\Models\Event;
use App\Models\Member;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\Reports\AttendanceRecapBuilder;
use App\Services\Reports\EventStatistics;
use App\Services\Reports\MemberStatistics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class ReportingTest extends TestCase
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

    private function record(Event $event, Member $member, AttendanceStatus $status): AttendanceRecord
    {
        return AttendanceRecord::factory()->create([
            'event_id' => $event->id,
            'member_id' => $member->id,
            'status' => $status,
            'method' => AttendanceMethod::Manual,
            'checked_in_at' => now(),
        ]);
    }

    public function test_rekap_absensi_menghitung_jumlah_per_status(): void
    {
        $event = Event::factory()->create();
        $m1 = Member::factory()->create();
        $m2 = Member::factory()->create();
        $m3 = Member::factory()->create();

        $this->record($event, $m1, AttendanceStatus::Hadir);
        $this->record($event, $m2, AttendanceStatus::Izin);
        $this->record($event, $m3, AttendanceStatus::Alpa);

        $summary = app(AttendanceRecapBuilder::class)->summaryCounts($event->id);

        $this->assertSame(1, $summary['hadir']);
        $this->assertSame(1, $summary['izin']);
        $this->assertSame(0, $summary['sakit']);
        $this->assertSame(1, $summary['alpa']);
    }

    public function test_rekap_per_anggota_menghitung_persentase_hadir(): void
    {
        $member = Member::factory()->create();

        $this->record(Event::factory()->create(), $member, AttendanceStatus::Hadir);
        $this->record(Event::factory()->create(), $member, AttendanceStatus::Izin);
        $this->record(Event::factory()->create(), $member, AttendanceStatus::Hadir);
        $this->record(Event::factory()->create(), $member, AttendanceStatus::Sakit);

        $rows = app(AttendanceRecapBuilder::class)->perMember();

        $this->assertCount(1, $rows);
        $this->assertSame(2, $rows[0]['hadir']);
        $this->assertSame(4, $rows[0]['total']);
        $this->assertSame(50.0, $rows[0]['persentase']);
    }

    public function test_rekap_filter_periode_membatasi_hasil(): void
    {
        $event = Event::factory()->create();
        $inside = Member::factory()->create();
        $outside = Member::factory()->create();

        AttendanceRecord::factory()->create([
            'event_id' => $event->id,
            'member_id' => $inside->id,
            'status' => AttendanceStatus::Hadir,
            'checked_in_at' => '2026-03-15 10:00:00',
        ]);
        AttendanceRecord::factory()->create([
            'event_id' => $event->id,
            'member_id' => $outside->id,
            'status' => AttendanceStatus::Hadir,
            'checked_in_at' => '2026-05-20 10:00:00',
        ]);

        $rows = app(AttendanceRecapBuilder::class)->perMember(null, '2026-03-01', '2026-03-31');

        $this->assertCount(1, $rows);
        $this->assertSame($inside->full_name, $rows[0]['member']);
    }

    public function test_rekap_filter_kelas(): void
    {
        $class = SchoolClass::factory()->create(['name' => 'Kelas Satu']);
        $other = SchoolClass::factory()->create(['name' => 'Kelas Dua']);
        $member = Member::factory()->create(['class_id' => $class->id]);
        $otherMember = Member::factory()->create(['class_id' => $other->id]);

        $this->record(Event::factory()->create(), $member, AttendanceStatus::Hadir);
        $this->record(Event::factory()->create(), $otherMember, AttendanceStatus::Hadir);

        $rows = app(AttendanceRecapBuilder::class)->perMember(null, null, null, $class->id);

        $this->assertCount(1, $rows);
        $this->assertSame('Kelas Satu', $rows[0]['kelas']);
    }

    public function test_halaman_laporan_absensi_tampil_untuk_pengurus(): void
    {
        Livewire::actingAs($this->pengurus())
            ->test(AttendanceReport::class)
            ->assertOk()
            ->assertSee('Laporan Rekap Absensi');
    }

    public function test_anggota_ditolak_laporan_absensi(): void
    {
        $this->actingAs($this->anggota())
            ->get(route('reports.attendance'))
            ->assertForbidden();
    }

    public function test_export_excel_rekap_absensi_dan_audit_log(): void
    {
        Excel::fake();
        $pengurus = $this->pengurus();

        Livewire::actingAs($pengurus)
            ->test(AttendanceReport::class)
            ->call('exportExcel');

        Excel::assertDownloaded('rekap-absensi.xlsx');

        $this->assertDatabaseHas('audit_logs', ['action' => 'export']);
        $log = AuditLog::where('action', 'export')->firstOrFail();
        $this->assertSame('attendance_recap', $log->new_values['resource']);
        $this->assertSame('excel', $log->new_values['format']);
    }

    public function test_export_pdf_rekap_absensi_menghasilkan_file_dan_audit(): void
    {
        $pengurus = $this->pengurus();
        $event = Event::factory()->create();
        $member = Member::factory()->create();
        $this->record($event, $member, AttendanceStatus::Hadir);

        $response = $this->actingAs($pengurus)->get(route('reports.attendance.pdf', ['event' => $event->id]));

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));

        $log = AuditLog::where('action', 'export')->firstOrFail();
        $this->assertSame('pdf', $log->new_values['format']);
    }

    public function test_anggota_ditolak_export_pdf(): void
    {
        $this->actingAs($this->anggota())
            ->get(route('reports.attendance.pdf'))
            ->assertForbidden();
    }

    public function test_statistik_anggota_per_kelas_dan_status(): void
    {
        $class = SchoolClass::factory()->create(['name' => 'Kelas A']);
        Member::factory()->count(2)->create(['class_id' => $class->id, 'status' => MemberStatus::Aktif]);
        Member::factory()->create(['status' => MemberStatus::Alumni]);

        $stats = app(MemberStatistics::class)->build();

        $this->assertSame(3, $stats['total']);
        $this->assertSame(2, collect($stats['per_class'])->firstWhere('label', 'Kelas A')['value']);

        $aktif = collect($stats['per_status'])->firstWhere('label', 'aktif');
        $this->assertSame(2, $aktif['value']);
    }

    public function test_widget_statistik_anggota_dan_kegiatan_tampil(): void
    {
        Member::factory()->create();

        Livewire::actingAs($this->pengurus())
            ->test(MemberStats::class)
            ->assertOk()
            ->assertSee('Statistik Anggota');

        Livewire::actingAs($this->pengurus())
            ->test(EventStats::class)
            ->assertOk()
            ->assertSee('Statistik Kegiatan');
    }

    public function test_statistik_kegiatan_per_jenis_dan_rasio_kehadiran(): void
    {
        $event = Event::factory()->create([
            'type' => EventType::Pelatihan,
            'starts_at' => '2026-06-10 09:00:00',
        ]);
        $event2 = Event::factory()->create([
            'type' => EventType::Pelatihan,
            'starts_at' => '2026-07-10 09:00:00',
        ]);

        $m1 = Member::factory()->create();
        $m2 = Member::factory()->create();
        $this->record($event, $m1, AttendanceStatus::Hadir);
        $this->record($event2, $m2, AttendanceStatus::Alpa);

        $stats = app(EventStatistics::class)->build(2026);

        $this->assertSame(2, collect($stats['per_type'])->firstWhere('label', 'pelatihan')['value']);
        $this->assertSame(1, collect($stats['per_month'])->firstWhere('label', 'Jun')['value']);
        $this->assertSame(1, collect($stats['per_month'])->firstWhere('label', 'Jul')['value']);
        $this->assertSame(50.0, $stats['attendance_ratio']);
    }

    public function test_rasio_kehadiran_nol_saat_tanpa_data(): void
    {
        $this->assertSame(0.0, app(EventStatistics::class)->attendanceRatio());
    }

    public function test_laporan_gabungan_generate_preview(): void
    {
        $event = Event::factory()->create();
        $member = Member::factory()->create();
        $this->record($event, $member, AttendanceStatus::Hadir);

        Livewire::actingAs($this->pengurus())
            ->test(ReportIndex::class)
            ->set('reportType', 'absensi')
            ->call('generate')
            ->assertSet('generated', true)
            ->assertSee('Detail Rekap Absensi');
    }

    public function test_laporan_gabungan_validasi_jenis(): void
    {
        Livewire::actingAs($this->pengurus())
            ->test(ReportIndex::class)
            ->set('reportType', 'tidak-ada')
            ->call('generate')
            ->assertHasErrors(['reportType']);
    }

    public function test_anggota_ditolak_route_laporan_dan_statistik(): void
    {
        $anggota = $this->anggota();

        foreach (['reports.index', 'reports.attendance', 'stats.members', 'stats.events'] as $route) {
            $this->actingAs($anggota)->get(route($route))->assertForbidden();
        }
    }

    public function test_pengurus_dapat_buka_route_laporan_dan_statistik(): void
    {
        $pengurus = $this->pengurus();

        foreach (['reports.index', 'reports.attendance', 'stats.members', 'stats.events'] as $route) {
            $this->actingAs($pengurus)->get(route($route))->assertOk();
        }
    }
}
