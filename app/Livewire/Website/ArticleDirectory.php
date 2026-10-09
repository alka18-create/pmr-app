<?php

namespace App\Livewire\Website;

use App\Enums\ArticleCategory;
use App\Models\Article;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.public', ['title' => 'Berita'])]
class ArticleDirectory extends Component
{
    use WithPagination;

    public string $categoryFilter = '';

    public function updated(string $property): void
    {
        if ($property === 'categoryFilter') {
            $this->resetPage();
        }
    }

    public function render()
    {
        $articles = Article::query()
            ->where('is_public', true)
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->when($this->categoryFilter !== '', fn ($q) => $q->where('category', $this->categoryFilter))
            ->orderByDesc('published_at')
            ->paginate(9);

        return view('livewire.website.article-directory', [
            'articles' => $articles,
            'categories' => ArticleCategory::cases(),
        ]);
    }
}
