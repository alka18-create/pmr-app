<?php

namespace App\Livewire\Materials;

use App\Enums\MaterialCategory;
use App\Enums\MaterialLevel;
use App\Models\Material;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.panel')]
class MaterialBrowse extends Component
{
    use AuthorizesRequests;

    public string $search = '';

    public string $categoryFilter = '';

    public string $levelFilter = '';

    public function mount(): void
    {
        $this->authorize('viewAny', Material::class);
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
            ->get();

        return view('livewire.materials.material-browse', [
            'materials' => $materials,
            'categories' => MaterialCategory::cases(),
            'levels' => MaterialLevel::cases(),
            'canManage' => auth()->user()?->can('manage', Material::class) ?? false,
        ]);
    }
}
