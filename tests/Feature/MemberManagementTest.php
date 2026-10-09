<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Members\MemberCreate;
use App\Livewire\Members\MemberEdit;
use App\Livewire\Members\MemberIndex;
use App\Models\AuditLog;
use App\Models\Batch;
use App\Models\Member;
use App\Models\Position;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MemberManagementTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_anggota_biasa_ditolak_halaman_anggota(): void
    {
        $user = $this->anggota();

        foreach (['members.index', 'members.create'] as $route) {
            $this->actingAs($user)->get(route($route))->assertForbidden();
        }

        $member = Member::factory()->create();
        $this->actingAs($user)->get(route('members.show', $member))->assertForbidden();
        $this->actingAs($user)->get(route('members.edit', $member))->assertForbidden();
    }

    public function test_pengurus_dapat_akses_dan_crud_anggota(): void
    {
        $pengurus = $this->pengurus();
        Member::factory()->create(['full_name' => 'Siti Aminah']);

        $this->actingAs($pengurus)
            ->get(route('members.index'))
            ->assertOk()
            ->assertSee('Siti Aminah')
            ->assertSee('Tambah Anggota');

        Batch::factory()->create(['name' => 'Angkatan Khusus', 'year' => 2028]);
        $class = SchoolClass::factory()->create();
        $position = Position::factory()->create();

        Livewire::actingAs($pengurus)
            ->test(MemberCreate::class)
            ->set('full_name', 'Rina Marlina')
            ->set('gender', 'perempuan')
            ->set('batch_id', Batch::firstWhere('name', 'Angkatan Khusus')->id)
            ->set('class_id', $class->id)
            ->set('position_id', $position->id)
            ->set('nis', '10001')
            ->set('nisn', '2000000001')
            ->set('joined_at', now()->toDateString())
            ->set('status', 'aktif')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('members', ['full_name' => 'Rina Marlina', 'nis' => '10001']);
    }

    public function test_admin_dapat_akses_anggota(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('members.index'))->assertOk();
        $this->actingAs($admin)->get(route('members.create'))->assertOk();
    }

    public function test_update_member_menghasilkan_audit_log(): void
    {
        $admin = $this->admin();
        $member = Member::factory()->create(['full_name' => 'Nama Awal']);

        Livewire::actingAs($admin)
            ->test(MemberEdit::class, ['member' => $member])
            ->set('full_name', 'Nama Baru Diubah')
            ->set('gender', $member->gender->value)
            ->set('batch_id', $member->batch_id)
            ->set('class_id', $member->class_id)
            ->set('position_id', $member->position_id)
            ->set('status', $member->status->value)
            ->set('joined_at', $member->joined_at->toDateString())
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('members', ['id' => $member->id, 'full_name' => 'Nama Baru Diubah']);

        $log = AuditLog::where('model_type', $member->getMorphClass())
            ->where('action', 'updated')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('full_name', $log->new_values ?? []);
        $this->assertStringNotContainsString('nisn', json_encode($log->new_values ?? []), 'NISN tidak boleh masuk audit');
    }

    public function test_create_member_menghasilkan_audit_log(): void
    {
        $pengurus = $this->pengurus();
        $batch = Batch::factory()->create();
        $class = SchoolClass::factory()->create();
        $position = Position::factory()->create();

        Livewire::actingAs($pengurus)
            ->test(MemberCreate::class)
            ->set('full_name', 'Audit Created Member')
            ->set('gender', 'laki')
            ->set('batch_id', $batch->id)
            ->set('class_id', $class->id)
            ->set('position_id', $position->id)
            ->set('nisn', '9999999999')
            ->set('joined_at', now()->toDateString())
            ->set('status', 'aktif')
            ->call('save')
            ->assertHasNoErrors();

        $member = Member::where('full_name', 'Audit Created Member')->first();
        $log = AuditLog::where('action', 'created')
            ->where('model_id', $member->id)
            ->first();

        $this->assertNotNull($log);
        $this->assertStringNotContainsString('nisn', json_encode($log->new_values ?? []), 'NISN harus difilter');
    }

    public function test_toggle_status_nonaktif_tanpa_hapus(): void
    {
        $admin = $this->admin();
        $member = Member::factory()->create(['status' => 'aktif']);

        Livewire::actingAs($admin)
            ->test(MemberIndex::class)
            ->call('toggleStatus', $member->id);

        $member->refresh();
        $this->assertSame('nonaktif', $member->status->value);
        $this->assertDatabaseHas('members', ['id' => $member->id]);
    }

    public function test_filter_search_dan_status_di_indeks(): void
    {
        $pengurus = $this->pengurus();
        $class = SchoolClass::factory()->create(['name' => 'X IPA Filter']);
        Member::factory()->create([
            'full_name' => 'Filter Target',
            'class_id' => $class->id,
            'status' => 'aktif',
        ]);
        Member::factory()->create(['full_name' => 'Filter Lain', 'status' => 'alumni']);

        Livewire::actingAs($pengurus)
            ->test(MemberIndex::class)
            ->set('search', 'Filter Target')
            ->assertSee('Filter Target')
            ->assertDontSee('Filter Lain');

        Livewire::actingAs($pengurus)
            ->test(MemberIndex::class)
            ->set('statusFilter', 'alumni')
            ->assertSee('Filter Lain')
            ->assertDontSee('Filter Target');
    }

    public function test_member_show_internal_hanya_pengurus_admin(): void
    {
        $member = Member::factory()->create([
            'nisn' => 'INTERNAL-NISN-123',
            'address' => 'Jl. Alamat Internal Rahasia 45',
            'phone' => '081111111111',
        ]);

        $pengurus = $this->pengurus();
        $this->actingAs($pengurus)
            ->get(route('members.show', $member))
            ->assertOk()
            ->assertSee('INTERNAL-NISN-123')
            ->assertSee('data internal terlihat');

        $anggota = $this->anggota();
        $this->actingAs($anggota)
            ->get(route('members.show', $member))
            ->assertForbidden();
    }

    public function test_validasi_member_request_duplikat_nisn(): void
    {
        $admin = $this->admin();
        Member::factory()->create(['nisn' => '1112223334']);
        $batch = Batch::factory()->create();
        $class = SchoolClass::factory()->create();
        $position = Position::factory()->create();

        Livewire::actingAs($admin)
            ->test(MemberCreate::class)
            ->set('full_name', 'Duplikat')
            ->set('gender', 'laki')
            ->set('batch_id', $batch->id)
            ->set('class_id', $class->id)
            ->set('position_id', $position->id)
            ->set('nisn', '1112223334')
            ->set('joined_at', now()->toDateString())
            ->set('status', 'aktif')
            ->call('save')
            ->assertHasErrors(['nisn' => 'unique']);
    }
}
