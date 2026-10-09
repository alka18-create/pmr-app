<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Programs\ProgramIndex;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProgramManagementTest extends TestCase
{
    use RefreshDatabase;

    private function pengurus(): User
    {
        return User::factory()->create(['role' => UserRole::Pengurus]);
    }

    public function test_pengurus_dapat_crud_program(): void
    {
        $pengurus = $this->pengurus();

        Livewire::actingAs($pengurus)
            ->test(ProgramIndex::class)
            ->set([
                'title' => 'Program Kerja 2026',
                'year' => 2026,
                'description' => 'Deskripsi',
                'phase' => 'Q1',
                'status' => 'rencana',
            ])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('programs', [
            'title' => 'Program Kerja 2026',
            'year' => 2026,
            'owner_id' => $pengurus->id,
        ]);

        $id = Program::first()->id;

        Livewire::actingAs($pengurus)
            ->test(ProgramIndex::class)
            ->call('edit', $id)
            ->set('title', 'Program Kerja 2026 Updated')
            ->call('save');

        $this->assertDatabaseHas('programs', ['id' => $id, 'title' => 'Program Kerja 2026 Updated']);

        Livewire::actingAs($pengurus)
            ->test(ProgramIndex::class)
            ->call('delete', $id)
            ->assertRedirect(route('programs.index'));

        $this->assertDatabaseMissing('programs', ['id' => $id]);
    }

    public function test_anggota_ditolak_program(): void
    {
        $anggota = User::factory()->create(['role' => UserRole::Anggota]);

        $this->actingAs($anggota)->get(route('programs.index'))->assertForbidden();
    }

    public function test_validasi_program_wajib_judul_dan_tahun(): void
    {
        Livewire::actingAs($this->pengurus())
            ->test(ProgramIndex::class)
            ->set(['title' => '', 'year' => 0])
            ->call('save')
            ->assertHasErrors(['title', 'year']);
    }
}
