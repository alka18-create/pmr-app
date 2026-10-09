<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Masters\BatchIndex;
use App\Livewire\Masters\ClassIndex;
use App\Livewire\Masters\PositionIndex;
use App\Models\Batch;
use App\Models\Member;
use App\Models\Position;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => UserRole::Admin]);
    }

    public function test_non_admin_ditolak_akses_master(): void
    {
        foreach ([UserRole::Pengurus, UserRole::Anggota] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)->get(route('admin.master.batches'))->assertForbidden();
            $this->actingAs($user)->get(route('admin.master.classes'))->assertForbidden();
            $this->actingAs($user)->get(route('admin.master.positions'))->assertForbidden();
        }
    }

    public function test_admin_dapat_membuka_halaman_master(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.master.batches'))->assertOk()->assertSee('Master Angkatan');
        $this->actingAs($admin)->get(route('admin.master.classes'))->assertOk()->assertSee('Master Kelas');
        $this->actingAs($admin)->get(route('admin.master.positions'))->assertOk()->assertSee('Master Jabatan');
    }

    public function test_admin_dapat_crud_angkatan(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(BatchIndex::class)
            ->set('name', 'Angkatan 2027')
            ->set('year', 2027)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('batches', ['name' => 'Angkatan 2027', 'year' => 2027]);

        $batch = Batch::firstWhere('name', 'Angkatan 2027');

        Livewire::actingAs($admin)
            ->test(BatchIndex::class)
            ->call('edit', $batch->id)
            ->set('name', 'Angkatan 2027 Updated')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('batches', ['name' => 'Angkatan 2027 Updated']);

        Livewire::actingAs($admin)
            ->test(BatchIndex::class)
            ->call('delete', $batch->id);

        $this->assertDatabaseMissing('batches', ['id' => $batch->id]);
    }

    public function test_admin_dapat_crud_kelas(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(ClassIndex::class)
            ->set('name', 'X IPA 99')
            ->set('level', 'X')
            ->set('year', 2026)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('classes', ['name' => 'X IPA 99', 'level' => 'X']);

        $class = SchoolClass::firstWhere('name', 'X IPA 99');

        Livewire::actingAs($admin)
            ->test(ClassIndex::class)
            ->call('delete', $class->id);

        $this->assertDatabaseMissing('classes', ['id' => $class->id]);
    }

    public function test_admin_dapat_crud_jabatan(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(PositionIndex::class)
            ->set('name', 'Wakil Ketua Baru')
            ->set('description', 'Membantu ketua')
            ->set('level', 2)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('positions', ['name' => 'Wakil Ketua Baru', 'level' => 2]);

        $position = Position::firstWhere('name', 'Wakil Ketua Baru');

        Livewire::actingAs($admin)
            ->test(PositionIndex::class)
            ->call('delete', $position->id);

        $this->assertDatabaseMissing('positions', ['id' => $position->id]);
    }

    public function test_validasi_master_duplikat(): void
    {
        $admin = $this->admin();
        Batch::factory()->create(['name' => 'Angkatan Duplikat', 'year' => 2030]);

        Livewire::actingAs($admin)
            ->test(BatchIndex::class)
            ->set('name', 'Angkatan Duplikat')
            ->set('year', 2031)
            ->call('save')
            ->assertHasErrors(['name' => 'unique']);
    }

    public function test_master_tidak_bisa_dihapus_bila_masih_dipakai(): void
    {
        $admin = $this->admin();
        $member = Member::factory()->create();

        Livewire::actingAs($admin)
            ->test(BatchIndex::class)
            ->call('delete', $member->batch_id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('batches', ['id' => $member->batch_id]);
    }
}
