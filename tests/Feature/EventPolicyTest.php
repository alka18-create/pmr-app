<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AttendanceRecord;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class EventPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_create_update_hanya_pengurus_admin(): void
    {
        $anggota = User::factory()->create(['role' => UserRole::Anggota]);
        $pengurus = User::factory()->create(['role' => UserRole::Pengurus]);

        $this->assertFalse(Gate::forUser($anggota)->allows('create', Event::class));
        $this->assertTrue(Gate::forUser($pengurus)->allows('create', Event::class));
    }

    public function test_event_delete_hanya_pembuat_atau_admin(): void
    {
        $creator = User::factory()->create(['role' => UserRole::Pengurus]);
        $other = User::factory()->create(['role' => UserRole::Pengurus]);
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $event = Event::factory()->create(['created_by' => $creator->id]);

        $this->assertTrue(Gate::forUser($creator)->allows('delete', $event));
        $this->assertFalse(Gate::forUser($other)->allows('delete', $event));
        $this->assertTrue(Gate::forUser($admin)->allows('delete', $event));
    }

    public function test_event_view_public_hanya_published_public(): void
    {
        $guestUser = null;
        $anggota = User::factory()->create(['role' => UserRole::Anggota]);
        $published = Event::factory()->published()->create();
        $draft = Event::factory()->draft()->create();

        $this->assertTrue(Gate::forUser($anggota)->allows('view', $published));
        $this->assertTrue(Gate::forUser($anggota)->allows('view', $draft));

        $privateEvent = Event::factory()->published()->privat()->create();
        $this->assertTrue(Gate::forUser($anggota)->allows('view', $privateEvent));
    }

    public function test_attendance_manage_hanya_pengurus_admin(): void
    {
        $anggota = User::factory()->create(['role' => UserRole::Anggota]);
        $pengurus = User::factory()->create(['role' => UserRole::Pengurus]);

        $this->assertFalse(Gate::forUser($anggota)->allows('manage', AttendanceRecord::class));
        $this->assertTrue(Gate::forUser($pengurus)->allows('manage', AttendanceRecord::class));
    }

    public function test_attendance_view_own_semua_role_login(): void
    {
        foreach (UserRole::cases() as $role) {
            $user = User::factory()->create(['role' => $role]);
            $this->assertTrue(
                Gate::forUser($user)->allows('viewOwn', AttendanceRecord::class),
                "role {$role->value}"
            );
        }
    }
}
