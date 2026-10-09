<?php

namespace App\Livewire\Articles;

use App\Enums\ArticleCategory;
use App\Enums\ArticleStatus;
use App\Models\Article;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.panel')]
class ArticleIndex extends Component
{
    use AuthorizesRequests, WithPagination;

    public string $search = '';

    public string $categoryFilter = '';

    public string $statusFilter = '';

    public function mount(): void
    {
        $this->authorize('create', Article::class);
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'categoryFilter', 'statusFilter'], true)) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $articles = Article::query()
            ->with('author:id,name')
            ->when($this->search !== '', function ($query) {
                $term = '%'.mb_strtolower($this->search).'%';
                $query->whereRaw('LOWER(title) LIKE ?', [$term]);
            })
            ->when($this->categoryFilter !== '', fn ($query) => $query->where('category', $this->categoryFilter))
            ->when($this->statusFilter !== '', fn ($query) => $query->where('status', $this->statusFilter))
            ->latest()
            ->paginate(10);

        return view('livewire.articles.article-index', [
            'articles' => $articles,
            'categories' => ArticleCategory::cases(),
            'statuses' => ArticleStatus::cases(),
        ]);
    }
}
