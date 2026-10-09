<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Events\EventCreate;
use App\Livewire\Events\EventShow;
use App\Models\AttendanceRecord;
use App\Models\Event;
use App\Models\EventParticipant;
use App\Models\Member;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class EventManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('check-in:127.0.0.1');
        RateLimiter::clear('check-in::1');
    }

    private function pengurus(): User
    {
        return User::factory()->create(['role' => UserRole::Pengurus]);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => UserRole::Admin]);
    }

    private function anggota(): User
    {
        return User::factory()->create(['role' => UserRole::Anggota]);
    }

    public function test_anggota_ditolak_halaman_kegiatan_panel(): void
    {
        $user = $this->anggota();
        $event = Event::factory()->create();

        foreach (['events.index', 'events.create', 'programs.index', 'attendance.recap'] as $route) {
            $this->actingAs($user)->get(route($route))->assertForbidden();
        }

        $this->actingAs($user)->get(route('events.show', $event))->assertForbidden();
    }

    public function test_pengurus_dapat_buat_kegiatan(): void
    {
        $pengurus = $this->pengurus();

        Livewire::actingAs($pengurus)
            ->test(EventCreate::class)
            ->set([
                'title' => 'Latihan P3K',
                'type' => 'pelatihan',
                'description' => 'Latihan dasar P3K',
                'location' => 'Aula',
                'starts_at' => now()->addWeek()->format('Y-m-d\TH:i'),
                'ends_at' => now()->addWeek()->addHours(2)->format('Y-m-d\TH:i'),
                'status' => 'published',
                'is_public' => true,
            ])
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('events', [
            'title' => 'Latihan P3K',
            'type' => 'pelatihan',
            'status' => 'published',
            'created_by' => $pengurus->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'created',
            'user_id' => $pengurus->id,
        ]);
    }

    public function test_anggota_ditolak_buat_kegiatan(): void
    {
        $anggota = $this->anggota();

        Livewire::actingAs($anggota)
            ->test(EventCreate::class)
            ->assertForbidden();
    }

    public function test_pengurus_dapat_tambah_peserta(): void
    {
        $pengurus = $this->pengurus();
        $event = Event::factory()->create(['created_by' => $pengurus->id]);
        $member = Member::factory()->create();

        Livewire::actingAs($pengurus)
            ->test(EventShow::class, ['event' => $event])
            ->set('memberSearch', (string) $member->id)
            ->call('addParticipant')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('event_participants', [
            'event_id' => $event->id,
            'member_id' => $member->id,
        ]);

        $participant = EventParticipant::where('event_id', $event->id)->first();
        $this->assertNotEmpty($participant->qr_token);
        $this->assertTrue(Str::isUuid($participant->qr_token));
    }

    public function test_pengurus_dapat_tambah_peserta_massal_per_kelas(): void
    {
        $pengurus = $this->pengurus();
        $event = Event::factory()->create(['created_by' => $pengurus->id]);
        $class = SchoolClass::factory()->create();
        $m1 = Member::factory()->create(['class_id' => $class->id]);
        $m2 = Member::factory()->create(['class_id' => $class->id]);

        Livewire::actingAs($pengurus)
            ->test(EventShow::class, ['event' => $event])
            ->set('bulkClassId', (string) $class->id)
            ->call('addParticipantsByClass')
            ->assertHasNoErrors();

        $this->assertSame(2, EventParticipant::where('event_id', $event->id)->count());

        Livewire::actingAs($pengurus)
            ->test(EventShow::class, ['event' => $event])
            ->set('bulkClassId', (string) $class->id)
            ->call('addParticipantsByClass');

        $this->assertSame(2, EventParticipant::where('event_id', $event->id)->count());
        $this->assertNotEmpty($m1->id);
        $this->assertNotEmpty($m2->id);
    }

    public function test_absensi_manual_hanya_pengurus(): void
    {
        $pengurus = $this->pengurus();
        $anggota = $this->anggota();
        $event = Event::factory()->create(['created_by' => $pengurus->id]);
        $member = Member::factory()->create();

        $this->actingAs($anggota)->get(route('events.show', $event))->assertForbidden();

        Livewire::actingAs($anggota)
            ->test(EventShow::class, ['event' => $event])
            ->set([
                'attendanceMemberId' => (string) $member->id,
                'attendanceStatus' => 'hadir',
            ])
            ->call('saveAttendance')
            ->assertForbidden();

        Livewire::actingAs($pengurus)
            ->test(EventShow::class, ['event' => $event])
            ->set([
                'attendanceMemberId' => (string) $member->id,
                'attendanceStatus' => 'hadir',
                'attendanceNotes' => 'Hadir tepat waktu',
            ])
            ->call('saveAttendance')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('attendance_records', [
            'event_id' => $event->id,
            'member_id' => $member->id,
            'status' => 'hadir',
            'method' => 'manual',
            'checked_in_by' => $pengurus->id,
        ]);
    }

    public function test_duplikat_absensi_event_member_ditolak_atau_update(): void
    {
        $pengurus = $this->pengurus();
        $event = Event::factory()->create(['created_by' => $pengurus->id]);
        $member = Member::factory()->create();

        AttendanceRecord::factory()->create([
            'event_id' => $event->id,
            'member_id' => $member->id,
            'status' => 'izin',
            'method' => 'manual',
            'checked_in_by' => $pengurus->id,
        ]);

        Livewire::actingAs($pengurus)
            ->test(EventShow::class, ['event' => $event])
            ->set([
                'attendanceMemberId' => (string) $member->id,
                'attendanceStatus' => 'sakit',
            ])
            ->call('saveAttendance');

        $this->assertSame(1, AttendanceRecord::where('event_id', $event->id)
            ->where('member_id', $member->id)
            ->count());
        $this->assertSame('sakit', AttendanceRecord::where('event_id', $event->id)
            ->where('member_id', $member->id)
            ->first()->status->value);
    }

    public function test_hapus_kegiatan_hanya_admin_atau_pembuat(): void
    {
        $otherPengurus = $this->pengurus();
        $creator = $this->pengurus();
        $admin = $this->admin();
        $event = Event::factory()->create(['created_by' => $creator->id]);

        Livewire::actingAs($otherPengurus)
            ->test(EventShow::class, ['event' => $event])
            ->call('delete')
            ->assertForbidden();

        $this->assertDatabaseHas('events', ['id' => $event->id]);

        Livewire::actingAs($admin)
            ->test(EventShow::class, ['event' => $event])
            ->call('delete')
            ->assertRedirect(route('events.index'));

        $this->assertSoftDeleted('events', ['id' => $event->id]);
    }

    public function test_qr_check_in_valid_menjadi_hadir(): void
    {
        $event = Event::factory()->published()->create([
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addHours(2),
        ]);
        $member = Member::factory()->create();
        $participant = EventParticipant::factory()->create([
            'event_id' => $event->id,
            'member_id' => $member->id,
        ]);

        $response = $this->get(route('check-in', $participant->qr_token));
        $response->assertOk();
        $response->assertSee('Berhasil Check-in');

        $this->assertDatabaseHas('attendance_records', [
            'event_id' => $event->id,
            'member_id' => $member->id,
            'status' => 'hadir',
            'method' => 'qr',
        ]);
    }

    public function test_qr_check_in_duplikat_ditolak(): void
    {
        $event = Event::factory()->published()->create([
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addHours(2),
        ]);
        $member = Member::factory()->create();
        $participant = EventParticipant::factory()->create([
            'event_id' => $event->id,
            'member_id' => $member->id,
        ]);

        $this->get(route('check-in', $participant->qr_token))->assertOk();

        $this->get(route('check-in', $participant->qr_token))->assertStatus(409);
        $this->assertSame(1, AttendanceRecord::where('event_id', $event->id)->count());
    }

    public function test_qr_check_in_token_salah_404(): void
    {
        $this->get(route('check-in', '00000000-0000-0000-0000-000000000000'))->assertNotFound();
    }

    public function test_qr_check_in_rate_limit(): void
    {
        $token = '11111111-1111-1111-1111-111111111111';

        for ($i = 0; $i < 10; $i++) {
            $this->get(route('check-in', $token))->assertNotFound();
        }

        $this->get(route('check-in', $token))->assertStatus(429);
    }

    public function test_halaman_publik_kegiatan_hanya_published_dan_public(): void
    {
        $published = Event::factory()->published()->create(['title' => 'Kegiatan Publik Terlihat']);
        $draft = Event::factory()->draft()->create(['title' => 'Draft Tersembunyi']);
        $private = Event::factory()->published()->privat()->create(['title' => 'Internal Tersembunyi']);
        $batal = Event::factory()->batal()->create(['title' => 'Batal Tersembunyi']);

        $response = $this->get(route('public.events.index'));
        $response->assertOk();
        $response->assertSee($published->title);
        $response->assertDontSee($draft->title);
        $response->assertDontSee($private->title);
        $response->assertDontSee($batal->title);

        $this->get(route('public.events.show', $draft->slug))->assertNotFound();
        $this->get(route('public.events.show', $published->slug))->assertOk();
    }

    public function test_pengurus_dapat_buka_index_kegiatan(): void
    {
        $this->actingAs($this->pengurus())->get(route('events.index'))->assertOk();
        $this->actingAs($this->admin())->get(route('events.index'))->assertOk();
    }

    public function test_upload_dokumentasi_validasi_mime(): void
    {
        $pengurus = $this->pengurus();
        $event = Event::factory()->create(['created_by' => $pengurus->id]);

        $file = UploadedFile::fake()->create('malware.php', 10, 'application/x-httpd-php');

        Livewire::actingAs($pengurus)
            ->test(EventShow::class, ['event' => $event])
            ->set('documentFile', $file)
            ->set('documentCaption', 'Foto kegiatan')
            ->call('uploadDocument')
            ->assertHasErrors(['documentFile']);
    }

    public function test_rekap_absensi_hanya_pengurus(): void
    {
        $this->actingAs($this->anggota())->get(route('attendance.recap'))->assertForbidden();
        $this->actingAs($this->pengurus())->get(route('attendance.recap'))->assertOk();
    }
}
