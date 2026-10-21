<?php

namespace App\Livewire\Masters;

use App\Actions\Masters\SaveBatch;
use App\Models\Batch;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('components.layouts.panel')]
class BatchIndex extends Component
{
    use AuthorizesRequests;

    public string $name = '';

    public $year = 2026;

    #[Locked]
    public ?int $editId = null;

    public function mount(): void
    {
        $this->authorize('manage-master');
    }

    public function edit(Batch $batch): void
    {
        $this->authorize('manage-master');

        $this->editId = $batch->id;
        $this->name = $batch->name;
        $this->year = $batch->year;
    }

    public function cancelEdit(): void
    {
        $this->reset(['editId', 'name', 'year']);
        $this->year = 2026;
    }

    public function save(SaveBatch $saveBatch)
    {
        $this->authorize('manage-master');

        $data = SaveBatch::validated([
            'id' => $this->editId,
            'name' => $this->name,
            'year' => (int) $this->year,
        ]);

        $saveBatch->execute($data);

        session()->flash('success', 'Angkatan berhasil disimpan.');
        $this->reset(['editId', 'name']);
        $this->year = 2026;

        return redirect()->route('admin.master.batches');
    }

    public function delete(Batch $batch, \App\Actions\Masters\DeleteBatch $deleteBatch)
    {
        $this->authorize('manage-master');

        if ($batch->members()->exists()) {
            session()->flash('error', 'Angkatan masih dipakai anggota dan tidak bisa dihapus.');

            return redirect()->route('admin.master.batches');
        }

        $deleteBatch->execute($batch);

        session()->flash('success', sprintf('Angkatan %s dihapus.', $batch->name));

        return redirect()->route('admin.master.batches');
    }

    public function render()
    {
        return view('livewire.masters.batch-index', [
            'batches' => Batch::orderByDesc('year')->orderBy('name')->get(),
        ]);
    }
}
