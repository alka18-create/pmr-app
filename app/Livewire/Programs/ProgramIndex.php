<?php

namespace App\Livewire\Programs;

use App\Enums\ProgramStatus;
use App\Models\Program;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.panel')]
class ProgramIndex extends Component
{
    use AuthorizesRequests;

    public string $title = '';

    public $year;

    public string $description = '';

    public string $phase = '';

    public string $status = 'rencana';

    public ?int $editId = null;

    public function mount(): void
    {
        Gate::authorize('access-pengurus-area');
        $this->year = (int) now()->year;
    }

    public function edit(int $id): void
    {
        Gate::authorize('access-pengurus-area');

        $program = Program::findOrFail($id);
        $this->editId = $program->id;
        $this->title = $program->title;
        $this->year = $program->year;
        $this->description = (string) $program->description;
        $this->phase = (string) $program->phase;
        $this->status = $program->status->value;
    }

    public function cancelEdit(): void
    {
        $this->reset(['editId', 'title', 'description', 'phase', 'status']);
        $this->year = (int) now()->year;
    }

    public function save()
    {
        Gate::authorize('access-pengurus-area');

        $validated = $this->validate([
            'title' => ['required', 'string', 'max:200'],
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'description' => ['nullable', 'string', 'max:10000'],
            'phase' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'string', 'in:rencana,berjalan,selesai'],
        ]);

        if ($this->editId) {
            Program::findOrFail($this->editId)->update($validated);
            session()->flash('success', 'Program diperbarui.');
        } else {
            Program::create($validated + ['owner_id' => auth()->id()]);
            session()->flash('success', 'Program ditambahkan.');
        }

        return redirect()->route('programs.index');
    }

    public function delete(int $id)
    {
        Gate::authorize('access-pengurus-area');
        Program::findOrFail($id)->delete();
        session()->flash('success', 'Program dihapus.');

        return redirect()->route('programs.index');
    }

    public function render()
    {
        return view('livewire.programs.program-index', [
            'programs' => Program::orderByDesc('year')->orderBy('title')->paginate(10),
            'statuses' => ProgramStatus::cases(),
        ]);
    }
}
