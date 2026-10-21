<?php

namespace App\Livewire\Articles;

use App\Actions\Articles\UpdateArticle;
use App\Enums\ArticleCategory;
use App\Enums\ArticleStatus;
use App\Models\Article;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.panel')]
class ArticleEdit extends Component
{
    use AuthorizesRequests, WithFileUploads;

    #[Locked]
    public int $articleId;

    public string $title = '';

    public string $excerpt = '';

    public string $content = '';

    public string $category = 'berita';

    public string $status = 'draft';

    public bool $is_public = true;

    public string $meta_title = '';

    public string $meta_description = '';

    public $cover = null;

    public function mount(Article $article): void
    {
        $this->authorize('update', $article);

        $this->articleId = $article->id;
        $this->title = $article->title;
        $this->excerpt = (string) $article->excerpt;
        $this->content = $article->content;
        $this->category = $article->category->value;
        $this->status = $article->status->value;
        $this->is_public = (bool) $article->is_public;
        $this->meta_title = (string) $article->meta_title;
        $this->meta_description = (string) $article->meta_description;
    }

    public function save(UpdateArticle $updateArticle)
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

        $article = Article::findOrFail($this->articleId);
        $this->authorize('update', $article);

        $updateArticle->execute($article, [
            'title' => $validated['title'],
            'excerpt' => $validated['excerpt'] ?? null,
            'content' => $validated['content'],
            'category' => $validated['category'],
            'status' => $validated['status'],
            'is_public' => (bool) ($validated['is_public'] ?? $this->is_public),
            'meta_title' => $validated['meta_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
        ], $this->cover instanceof \Illuminate\Http\UploadedFile ? $this->cover : null);

        session()->flash('success', 'Artikel berhasil diperbarui.');

        return redirect()->route('articles.edit', $article);
    }

    public function render()
    {
        return view('livewire.articles.article-edit', [
            'categories' => ArticleCategory::cases(),
            'statuses' => ArticleStatus::cases(),
        ]);
    }
}
