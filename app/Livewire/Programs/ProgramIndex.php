<?php

namespace App\Livewire\Programs;

use App\Actions\Programs\DeleteProgram;
use App\Actions\Programs\SaveProgram;
use App\Enums\ProgramStatus;
use App\Models\Program;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
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

    #[Locked]
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

    public function save(SaveProgram $saveProgram)
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
            $saveProgram->execute($validated, Program::findOrFail($this->editId));
            session()->flash('success', 'Program diperbarui.');
        } else {
            $saveProgram->execute($validated, null, auth()->id());
            session()->flash('success', 'Program ditambahkan.');
        }

        return redirect()->route('programs.index');
    }

    public function delete(int $id, DeleteProgram $deleteProgram)
    {
        Gate::authorize('access-pengurus-area');
        $deleteProgram->execute(Program::findOrFail($id));
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
