<?php

namespace App\Livewire\Materials;

use App\Actions\Materials\CreateMaterial;
use App\Actions\Materials\DeleteMaterial;
use App\Actions\Materials\UpdateMaterial;
use App\Enums\MaterialCategory;
use App\Enums\MaterialLevel;
use App\Models\Material;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('components.layouts.panel')]
class MaterialIndex extends Component
{
    use AuthorizesRequests, WithFileUploads, WithPagination;

    public string $search = '';

    public string $categoryFilter = '';

    public string $levelFilter = '';

    public string $title = '';

    public string $category = 'lainnya';

    public string $level = 'mula';

    public string $content = '';

    public int $order = 0;

    public bool $is_public = true;

    public $file = null;

    #[Locked]
    public ?int $editId = null;

    public function mount(): void
    {
        $this->authorize('manage', Material::class);
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'categoryFilter', 'levelFilter'], true)) {
            $this->resetPage();
        }
    }

    public function save()
    {
        if ($this->editId !== null) {
            $material = Material::findOrFail($this->editId);
            $this->authorize('manage', Material::class);
        } else {
            $this->authorize('manage', Material::class);
            $material = null;
        }

        $rules = [
            'title' => ['required', 'string', 'max:200'],
            'category' => ['required', Rule::enum(MaterialCategory::class)],
            'level' => ['required', Rule::enum(MaterialLevel::class)],
            'content' => ['nullable', 'string', 'max:50000'],
            'order' => ['required', 'integer', 'min:0', 'max:9999'],
            'is_public' => ['boolean'],
        ];

        if ($this->file) {
            $rules['file'] = ['required', 'file', 'mimes:pdf', 'max:5120'];
        } elseif ($material === null) {
            $rules['file'] = ['required', 'file', 'mimes:pdf', 'max:5120'];
        }

        $validated = $this->validate($rules, [
            'title.required' => 'Judul materi wajib diisi.',
            'category.required' => 'Kategori wajib dipilih.',
            'level.required' => 'Level wajib dipilih.',
            'order.required' => 'Urutan wajib diisi.',
            'file.required' => 'File PDF wajib diunggah.',
            'file.mimes' => 'Materi harus berformat PDF.',
            'file.max' => 'File materi maksimal 5 MB.',
        ]);

        $data = [
            'title' => $validated['title'],
            'category' => $validated['category'],
            'level' => $validated['level'],
            'content' => $validated['content'] ?? null,
            'order' => (int) $validated['order'],
            'is_public' => (bool) ($validated['is_public'] ?? $this->is_public),
            'uploaded_by' => auth()->id(),
        ];

        if ($material) {
            $data['uploaded_by'] = $material->uploaded_by;
            (new UpdateMaterial)->execute($material, $data);
            $saved = $material;
            $message = 'Materi diperbarui.';
        } else {
            $saved = (new CreateMaterial)->execute($data);
            $message = 'Materi berhasil ditambahkan.';
        }

        if ($this->file) {
            $path = $this->file->store('materials/'.$saved->id, 'public');
            (new UpdateMaterial)->execute($saved, ['file_path' => $path]);
        }

        $this->cancelEdit();

        session()->flash('success', $message);

        return redirect()->route('materials.manage');
    }

    public function startEdit(?int $id = null): void
    {
        if ($id === null) {
            $this->cancelEdit();

            return;
        }

        $material = Material::findOrFail($id);
        $this->authorize('manage', Material::class);

        $this->editId = $material->id;
        $this->title = $material->title;
        $this->category = $material->category->value;
        $this->level = $material->level->value;
        $this->content = (string) $material->content;
        $this->order = $material->order;
        $this->is_public = $material->is_public;
    }

    public function cancelEdit(): void
    {
        $this->editId = null;
        $this->reset(['title', 'content', 'file']);
        $this->category = 'lainnya';
        $this->level = 'mula';
        $this->order = 0;
        $this->is_public = true;
    }

    public function delete(int $id, DeleteMaterial $deleteMaterial): void
    {
        $material = Material::findOrFail($id);
        $this->authorize('manage', Material::class);

        $deleteMaterial->execute($material);

        session()->flash('success', 'Materi dihapus.');
    }

    public function render()
    {
        $materials = Material::query()
            ->when($this->search !== '', function ($query) {
                $term = '%'.mb_strtolower($this->search).'%';
                $query->whereRaw('LOWER(title) LIKE ?', [$term]);
            })
            ->when($this->categoryFilter !== '', fn ($query) => $query->where('category', $this->categoryFilter))
            ->when($this->levelFilter !== '', fn ($query) => $query->where('level', $this->levelFilter))
            ->orderBy('order')
            ->orderBy('id')
            ->paginate(10);

        return view('livewire.materials.material-index', [
            'materials' => $materials,
            'categories' => MaterialCategory::cases(),
            'levels' => MaterialLevel::cases(),
        ]);
    }
}
