<?php

namespace App\Livewire\Gallery;

use App\Actions\Gallery\CreateGalleryItem;
use App\Actions\Gallery\DeleteGalleryItem;
use App\Enums\GalleryFileType;
use App\Models\GalleryCategory;
use App\Models\GalleryItem;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('components.layouts.panel')]
class GalleryIndex extends Component
{
    use AuthorizesRequests, WithFileUploads, WithPagination;

    public string $search = '';

    public string $categoryFilter = '';

    // Create item
    public string $title = '';

    public string $description = '';

    public string $category_id = '';

    public string $file_type = 'image';

    public string $video_url = '';

    public $file = null;

    public bool $is_public = true;

    public ?string $taken_at = null;

    // Category CRUD
    public string $newCategoryName = '';

    public string $newCategoryDescription = '';

    #[Locked]
    public ?int $editItemId = null;

    #[Locked]
    public ?int $editCategoryId = null;

    public string $editCategoryName = '';

    public function mount(): void
    {
        $this->authorize('create', GalleryItem::class);
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'categoryFilter'], true)) {
            $this->resetPage();
        }
    }

    public function saveItem(CreateGalleryItem $createGalleryItem)
    {
        $rules = [
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:500'],
            'category_id' => ['nullable', Rule::exists('gallery_categories', 'id')],
            'file_type' => ['required', Rule::enum(GalleryFileType::class)],
            'video_url' => ['nullable', 'url', 'max:500', 'required_if:file_type,video'],
            'is_public' => ['boolean'],
            'taken_at' => ['nullable', 'date'],
        ];

        if ($this->file_type === 'image') {
            $rules['file'] = ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'];
        }

        $validated = $this->validate($rules, [
            'title.required' => 'Judul wajib diisi.',
            'file.required' => 'File gambar wajib diunggah.',
            'file.mimes' => 'File harus berformat JPG, PNG, atau WebP.',
            'file.max' => 'Ukuran file maksimal 4 MB.',
            'video_url.required_if' => 'URL video wajib diisi untuk tipe video.',
            'video_url.url' => 'URL video tidak valid.',
        ]);

        $data = [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'category_id' => $validated['category_id'] ?? null,
            'file_type' => $validated['file_type'],
            'video_url' => $validated['video_url'] ?? null,
            'is_public' => (bool) ($validated['is_public'] ?? $this->is_public),
            'taken_at' => $validated['taken_at'] ?? null,
            'uploaded_by' => auth()->id(),
        ];

        if ($this->file && $this->file_type === 'image') {
            $data['file_path'] = $this->file->store('gallery', 'public');
        }

        $createGalleryItem->execute($data);

        session()->flash('success', 'Item galeri berhasil ditambahkan.');
        $this->reset(['title', 'description', 'file', 'video_url', 'taken_at']);
        $this->is_public = true;
        $this->file_type = 'image';
    }

    public function deleteItem(int $itemId): void
    {
        $item = GalleryItem::findOrFail($itemId);
        $this->authorize('delete', $item);

        if ($item->file_path) {
            Storage::disk('public')->delete($item->file_path);
        }

        (new DeleteGalleryItem)->execute($item);

        session()->flash('success', 'Item galeri dihapus.');
    }

    public function saveCategory(\App\Actions\Gallery\SaveGalleryCategory $saveCategory)
    {
        $this->authorize('manageCategory', GalleryItem::class);

        $validated = $this->validate([
            'newCategoryName' => ['required', 'string', 'max:100'],
            'newCategoryDescription' => ['nullable', 'string', 'max:300'],
        ], [
            'newCategoryName.required' => 'Nama kategori wajib diisi.',
        ]);

        $saveCategory->execute([
            'name' => $validated['newCategoryName'],
            'description' => $validated['newCategoryDescription'] ?? null,
        ]);

        session()->flash('success', 'Kategori ditambahkan.');
        $this->reset(['newCategoryName', 'newCategoryDescription']);
    }

    public function startEditCategory(int $categoryId): void
    {
        $this->authorize('manageCategory', GalleryItem::class);

        $category = GalleryCategory::findOrFail($categoryId);
        $this->editCategoryId = $category->id;
        $this->editCategoryName = $category->name;
    }

    public function cancelEditCategory(): void
    {
        $this->reset(['editCategoryId', 'editCategoryName']);
    }

    public function updateCategory(\App\Actions\Gallery\SaveGalleryCategory $saveCategory): void
    {
        $this->authorize('manageCategory', GalleryItem::class);

        $this->validate([
            'editCategoryName' => ['required', 'string', 'max:100'],
        ], [
            'editCategoryName.required' => 'Nama kategori wajib diisi.',
        ]);

        $saveCategory->execute(['name' => $this->editCategoryName], $this->editCategoryId);

        session()->flash('success', 'Kategori diperbarui.');
        $this->reset(['editCategoryId', 'editCategoryName']);
    }

    public function deleteCategory(int $categoryId, \App\Actions\Gallery\DeleteGalleryCategory $deleteCategory): void
    {
        $this->authorize('manageCategory', GalleryItem::class);

        $category = GalleryCategory::findOrFail($categoryId);
        $deleteCategory->execute($category);

        session()->flash('success', 'Kategori dihapus.');
    }

    public function render()
    {
        $items = GalleryItem::query()
            ->with('category:id,name')
            ->when($this->search !== '', function ($query) {
                $term = '%'.mb_strtolower($this->search).'%';
                $query->whereRaw('LOWER(title) LIKE ?', [$term]);
            })
            ->when($this->categoryFilter !== '', fn ($query) => $query->where('category_id', $this->categoryFilter))
            ->latest()
            ->paginate(12);

        return view('livewire.gallery.gallery-index', [
            'items' => $items,
            'categories' => GalleryCategory::orderBy('name')->get(),
            'fileTypes' => GalleryFileType::cases(),
        ]);
    }
}
