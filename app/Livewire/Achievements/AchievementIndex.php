<?php

namespace App\Livewire\Achievements;

use App\Actions\Achievements\CreateAchievement;
use App\Actions\Achievements\DeleteAchievement;
use App\Actions\Achievements\UpdateAchievement;
use App\Enums\AchievementCategory;
use App\Enums\AchievementLevel;
use App\Models\Achievement;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('components.layouts.panel')]
class AchievementIndex extends Component
{
    use AuthorizesRequests, WithFileUploads, WithPagination;

    public string $search = '';

    public string $levelFilter = '';

    public string $yearFilter = '';

    // create form
    public string $title = '';

    public string $level = 'sekolah';

    public string $category = 'lomba';

    public $year = null;

    public string $description = '';

    public string $winner = '';

    public bool $is_public = true;

    public $attachment = null;

    #[Locked]
    public ?int $editId = null;

    public function mount(): void
    {
        $this->authorize('create', Achievement::class);
        $this->year = (int) now()->year;
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'levelFilter', 'yearFilter'], true)) {
            $this->resetPage();
        }
    }

    public function save()
    {
        if ($this->editId !== null) {
            $achievement = Achievement::findOrFail($this->editId);
            $this->authorize('update', $achievement);
        } else {
            $this->authorize('create', Achievement::class);
            $achievement = null;
        }

        $validated = $this->validate([
            'title' => ['required', 'string', 'max:200'],
            'level' => ['required', Rule::enum(AchievementLevel::class)],
            'category' => ['required', Rule::enum(AchievementCategory::class)],
            'year' => ['required', 'integer', 'min:1990', 'max:2100'],
            'description' => ['nullable', 'string', 'max:5000'],
            'winner' => ['nullable', 'string', 'max:200'],
            'is_public' => ['boolean'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ], [
            'title.required' => 'Judul wajib diisi.',
            'level.required' => 'Tingkat wajib dipilih.',
            'category.required' => 'Kategori wajib dipilih.',
            'year.required' => 'Tahun wajib diisi.',
            'attachment.mimes' => 'Lampiran harus JPG, PNG, atau PDF.',
            'attachment.max' => 'Lampiran maksimal 5 MB.',
        ]);

        $data = [
            'title' => $validated['title'],
            'level' => $validated['level'],
            'category' => $validated['category'],
            'year' => (int) $validated['year'],
            'description' => $validated['description'] ?? null,
            'winner' => $validated['winner'] ?? null,
            'is_public' => (bool) ($validated['is_public'] ?? $this->is_public),
        ];

        $attachment = $this->attachment instanceof \Illuminate\Http\UploadedFile ? $this->attachment : null;

        if ($achievement) {
            (new UpdateAchievement)->execute($achievement, $data, $attachment);
            session()->flash('success', 'Prestasi diperbarui.');
        } else {
            (new CreateAchievement)->execute($data, $attachment);
            session()->flash('success', 'Prestasi berhasil ditambahkan.');
        }

        $this->cancelEdit();
    }

    public function startEdit(?int $id = null): void
    {
        if ($id === null) {
            $this->cancelEdit();

            return;
        }

        $achievement = Achievement::findOrFail($id);
        $this->authorize('update', $achievement);

        $this->editId = $achievement->id;
        $this->title = $achievement->title;
        $this->level = $achievement->level->value;
        $this->category = $achievement->category->value;
        $this->year = $achievement->year;
        $this->description = (string) $achievement->description;
        $this->winner = (string) $achievement->winner;
        $this->is_public = (bool) $achievement->is_public;
    }

    public function cancelEdit(): void
    {
        $this->editId = null;
        $this->reset(['title', 'description', 'winner', 'attachment']);
        $this->is_public = true;
        $this->level = 'sekolah';
        $this->category = 'lomba';
        $this->year = (int) now()->year;
    }

    public function delete(int $id, DeleteAchievement $deleteAchievement): void
    {
        $achievement = Achievement::findOrFail($id);
        $this->authorize('delete', $achievement);

        $deleteAchievement->execute($achievement);

        session()->flash('success', 'Prestasi dihapus.');
    }

    public function render()
    {
        $achievements = Achievement::query()
            ->when($this->search !== '', function ($query) {
                $term = '%'.mb_strtolower($this->search).'%';
                $query->whereRaw('LOWER(title) LIKE ?', [$term]);
            })
            ->when($this->levelFilter !== '', fn ($query) => $query->where('level', $this->levelFilter))
            ->when($this->yearFilter !== '', fn ($query) => $query->where('year', $this->yearFilter))
            ->orderByDesc('year')
            ->orderByDesc('id')
            ->paginate(10);

        return view('livewire.achievements.achievement-index', [
            'achievements' => $achievements,
            'levels' => AchievementLevel::cases(),
            'categories' => AchievementCategory::cases(),
        ]);
    }
}
