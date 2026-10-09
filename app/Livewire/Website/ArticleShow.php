<?php

namespace App\Livewire\Website;

use App\Models\Article;
use App\Services\ContentSanitizer;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ArticleShow extends Component
{
    #[Locked]
    public string $slug = '';

    public function mount(string $slug): void
    {
        $this->slug = $slug;
    }

    public function render()
    {
        $article = Article::query()
            ->where('slug', $this->slug)
            ->where('is_public', true)
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->with('author:id,name')
            ->firstOrFail();

        $article->increment('views');

        return view('livewire.website.article-show', [
            'article' => [
                'id' => $article->id,
                'title' => $article->title,
                'slug' => $article->slug,
                'excerpt' => $article->excerpt,
                'content' => ContentSanitizer::sanitize($article->content),
                'category' => $article->category->value,
                'cover_path' => $article->cover_path,
                'published_at' => $article->published_at?->format('d M Y'),
                'author' => $article->author?->name,
                'views' => $article->views,
                'meta_title' => $article->metaTitle(),
                'meta_description' => $article->metaDescription(),
            ],
        ])->layout('components.layouts.public', [
            'title' => $article->metaTitle(),
            'description' => $article->metaDescription(),
            'url' => route('public.articles.show', $article->slug),
            'type' => 'article',
        ]);
    }
}
