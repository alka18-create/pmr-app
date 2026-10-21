<?php

namespace App\Livewire\Masters;

use App\Actions\Masters\SavePosition;
use App\Models\Position;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('components.layouts.panel')]
class PositionIndex extends Component
{
    use AuthorizesRequests;

    public string $name = '';

    public string $description = '';

    public $level = 5;

    #[Locked]
    public ?int $editId = null;

    public function mount(): void
    {
        $this->authorize('manage-master');
    }

    public function edit(Position $position): void
    {
        $this->authorize('manage-master');

        $this->editId = $position->id;
        $this->name = $position->name;
        $this->description = $position->description ?? '';
        $this->level = $position->level;
    }

    public function cancelEdit(): void
    {
        $this->reset(['editId', 'name', 'description']);
        $this->level = 5;
    }

    public function save(SavePosition $savePosition)
    {
        $this->authorize('manage-master');

        $data = SavePosition::validated([
            'id' => $this->editId,
            'name' => $this->name,
            'description' => $this->description ?: null,
            'level' => (int) $this->level,
        ]);

        $savePosition->execute($data);

        session()->flash('success', 'Jabatan berhasil disimpan.');
        $this->cancelEdit();

        return redirect()->route('admin.master.positions');
    }

    public function delete(Position $position, \App\Actions\Masters\DeletePosition $deletePosition)
    {
        $this->authorize('manage-master');

        if ($position->members()->exists() || $position->memberPositions()->exists()) {
            session()->flash('error', 'Jabatan masih dipakai anggota dan tidak bisa dihapus.');

            return redirect()->route('admin.master.positions');
        }

        $deletePosition->execute($position);

        session()->flash('success', sprintf('Jabatan %s dihapus.', $position->name));

        return redirect()->route('admin.master.positions');
    }

    public function render()
    {
        return view('livewire.masters.position-index', [
            'positions' => Position::orderBy('level')->orderBy('name')->get(),
        ]);
    }
}
