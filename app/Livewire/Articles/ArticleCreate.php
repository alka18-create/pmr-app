<?php

namespace App\Livewire\Articles;

use App\Actions\Articles\CreateArticle;
use App\Enums\ArticleCategory;
use App\Enums\ArticleStatus;
use App\Models\Article;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.panel')]
class ArticleCreate extends Component
{
    use AuthorizesRequests, WithFileUploads;

    public string $title = '';

    public string $excerpt = '';

    public string $content = '';

    public string $category = 'berita';

    public string $status = 'draft';

    public bool $is_public = true;

    public string $meta_title = '';

    public string $meta_description = '';

    public $cover = null;

    public function mount(): void
    {
        $this->authorize('create', Article::class);
    }

    public function save(CreateArticle $createArticle)
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:200'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string', 'max:50000'],
            'category' => ['required', Rule::enum(ArticleCategory::class)],
            'status' => ['required', Rule::enum(ArticleStatus::class)],
            'is_public' => ['boolean'],
            'meta_title' => ['nullable', 'string', 'max:200'],
            'meta_description' => ['nullable', 'string', 'max:300'],
            'cover' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'title.required' => 'Judul wajib diisi.',
            'content.required' => 'Isi konten wajib diisi.',
            'category.required' => 'Kategori wajib dipilih.',
            'status.required' => 'Status wajib dipilih.',
            'cover.mimes' => 'Cover harus berformat JPG, PNG, atau WebP.',
            'cover.max' => 'Cover maksimal 2 MB.',
        ]);

        $article = $createArticle->execute([
            'title' => $validated['title'],
            'excerpt' => $validated['excerpt'] ?? null,
            'content' => $validated['content'],
            'category' => $validated['category'],
            'status' => $validated['status'],
            'is_public' => (bool) ($validated['is_public'] ?? $this->is_public),
            'meta_title' => $validated['meta_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
            'author_id' => auth()->id(),
        ], $this->cover instanceof \Illuminate\Http\UploadedFile ? $this->cover : null);

        session()->flash('success', 'Artikel berhasil dibuat.');

        return redirect()->route('articles.edit', $article);
    }

    public function render()
    {
        return view('livewire.articles.article-create', [
            'categories' => ArticleCategory::cases(),
            'statuses' => ArticleStatus::cases(),
        ]);
    }
}
