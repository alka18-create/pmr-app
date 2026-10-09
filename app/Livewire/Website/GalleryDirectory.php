<?php

namespace App\Livewire\Website;

use App\Models\GalleryCategory;
use App\Models\GalleryItem;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.public', ['title' => 'Galeri'])]
class GalleryDirectory extends Component
{
    use WithPagination;

    #[Locked]
    public string $categorySlug = '';

    public function mount(string $categorySlug = ''): void
    {
        $this->categorySlug = $categorySlug;
    }

    public function render()
    {
        $category = null;

        $items = GalleryItem::query()
            ->where('is_public', true)
            ->with('category:id,name,slug')
            ->when($this->categorySlug !== '', function ($query) use (&$category) {
                $category = GalleryCategory::where('slug', $this->categorySlug)->firstOrFail();
                $query->where('category_id', $category->id);
            })
            ->latest()
            ->paginate(12);

        return view('livewire.website.gallery-directory', [
            'items' => $items,
            'categories' => GalleryCategory::orderBy('name')->get(['id', 'name', 'slug']),
            'category' => $category,
        ]);
    }
}
