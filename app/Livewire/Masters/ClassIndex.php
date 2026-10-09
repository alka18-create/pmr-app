<?php

namespace App\Livewire\Masters;

use App\Actions\Masters\SaveSchoolClass;
use App\Models\SchoolClass;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.panel')]
class ClassIndex extends Component
{
    use AuthorizesRequests;

    public string $name = '';

    public string $level = 'X';

    public $year = 2026;

    public ?int $editId = null;

    public function mount(): void
    {
        $this->authorize('manage-master');
    }

    public function edit(SchoolClass $schoolClass): void
    {
        $this->authorize('manage-master');

        $this->editId = $schoolClass->id;
        $this->name = $schoolClass->name;
        $this->level = $schoolClass->level;
        $this->year = $schoolClass->year;
    }

    public function cancelEdit(): void
    {
        $this->reset(['editId', 'name', 'level']);
        $this->year = 2026;
        $this->level = 'X';
    }

    public function save(SaveSchoolClass $saveSchoolClass)
    {
        $this->authorize('manage-master');

        $data = SaveSchoolClass::validated([
            'id' => $this->editId,
            'name' => $this->name,
            'level' => $this->level,
            'year' => (int) $this->year,
        ]);

        $saveSchoolClass->execute($data);

        session()->flash('success', 'Kelas berhasil disimpan.');
        $this->cancelEdit();

        return redirect()->route('admin.master.classes');
    }

    public function delete(SchoolClass $schoolClass)
    {
        $this->authorize('manage-master');

        if ($schoolClass->members()->exists()) {
            session()->flash('error', 'Kelas masih dipakai anggota dan tidak bisa dihapus.');

            return redirect()->route('admin.master.classes');
        }

        $schoolClass->delete();

        session()->flash('success', sprintf('Kelas %s dihapus.', $schoolClass->name));

        return redirect()->route('admin.master.classes');
    }

    public function render()
    {
        return view('livewire.masters.class-index', [
            'classes' => SchoolClass::orderBy('level')->orderBy('name')->get(),
        ]);
    }
}
