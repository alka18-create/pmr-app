<?php

namespace Tests\Feature;

use App\Enums\MemberStatus;
use App\Enums\UserRole;
use App\Livewire\Members\MemberIndex;
use App\Models\AuditLog;
use App\Models\Batch;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Tests\TestCase;

class AlumniManagementTest extends TestCase
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

    public function test_pengurus_dapat_bulk_tandai_alumni_per_angkatan(): void
    {
        $pengurus = $this->pengurus();
        $batch = Batch::factory()->create();
        $other = Batch::factory()->create();

        $a1 = Member::factory()->create(['batch_id' => $batch->id, 'status' => MemberStatus::Aktif]);
        $a2 = Member::factory()->create(['batch_id' => $batch->id, 'status' => MemberStatus::Aktif]);
        $nonaktif = Member::factory()->create(['batch_id' => $batch->id, 'status' => MemberStatus::Nonaktif]);
        $lain = Member::factory()->create(['batch_id' => $other->id, 'status' => MemberStatus::Aktif]);

        Livewire::actingAs($pengurus)
            ->test(MemberIndex::class)
            ->set('batchFilter', (string) $batch->id)
            ->call('markBatchAsAlumni')
            ->assertRedirect(route('members.index'));

        $this->assertSame(MemberStatus::Alumni, $a1->fresh()->status);
        $this->assertSame(MemberStatus::Alumni, $a2->fresh()->status);
        $this->assertSame(MemberStatus::Nonaktif, $nonaktif->fresh()->status);
        $this->assertSame(MemberStatus::Aktif, $lain->fresh()->status);

        $this->assertSame(
            2,
            AuditLog::where('action', 'updated')
                ->whereIn('model_id', [$a1->id, $a2->id])
                ->count()
        );
    }

    public function test_bulk_alumni_tanpa_pilih_angkatan_ditolak(): void
    {
        $pengurus = $this->pengurus();
        $member = Member::factory()->create(['status' => MemberStatus::Aktif]);

        Livewire::actingAs($pengurus)
            ->test(MemberIndex::class)
            ->call('markBatchAsAlumni')
            ->assertSessionHas('error')
            ->assertRedirect(route('members.index'));

        $this->assertSame(MemberStatus::Aktif, $member->fresh()->status);
    }

    public function test_anggota_biasa_ditolak_bulk_alumni(): void
    {
        $anggota = $this->anggota();

        $this->actingAs($anggota)->get(route('members.index'))->assertForbidden();

        $this->assertFalse(
            Gate::forUser($anggota)->allows('create', Member::class)
        );
    }

    public function test_kembalikan_alumni_ke_aktif_via_toggle(): void
    {
        $pengurus = $this->pengurus();
        $member = Member::factory()->create(['status' => MemberStatus::Alumni]);

        Livewire::actingAs($pengurus)
            ->test(MemberIndex::class)
            ->call('toggleStatus', $member->id)
            ->assertRedirect(route('members.index'));

        $this->assertSame(MemberStatus::Aktif, $member->fresh()->status);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'updated',
            'model_id' => $member->id,
        ]);
    }
}
