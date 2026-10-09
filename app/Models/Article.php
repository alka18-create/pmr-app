<?php

namespace App\Models;

use App\Enums\ArticleCategory;
use App\Enums\ArticleStatus;
use Database\Factories\ArticleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'title',
    'slug',
    'excerpt',
    'content',
    'category',
    'cover_path',
    'meta_title',
    'meta_description',
    'status',
    'is_public',
    'published_at',
    'author_id',
    'views',
])]
class Article extends Model
{
    /** @use HasFactory<ArticleFactory> */
    use HasFactory, SoftDeletes;

    protected $casts = [
        'category' => ArticleCategory::class,
        'status' => ArticleStatus::class,
        'is_public' => 'boolean',
        'published_at' => 'datetime',
        'views' => 'integer',
    ];

    public static function booted(): void
    {
        static::creating(function (Article $article) {
            if (blank($article->slug)) {
                $article->slug = static::generateUniqueSlug($article->title);
            }

            if ($article->status === ArticleStatus::Published && blank($article->published_at)) {
                $article->published_at = now();
            }
        });

        static::updating(function (Article $article) {
            if ($article->isDirty('status')
                && $article->status === ArticleStatus::Published
                && blank($article->published_at)) {
                $article->published_at = now();
            }
        });
    }

    public static function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $slug = $base !== '' ? $base : 'berita';
        $candidate = $slug;
        $i = 2;

        while (static::where('slug', $candidate)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $candidate = $slug.'-'.$i;
            $i++;
        }

        return $candidate;
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function isPubliclyVisible(): bool
    {
        return $this->is_public
            && $this->status === ArticleStatus::Published
            && ($this->published_at === null || $this->published_at->lte(now()));
    }

    public function metaTitle(): string
    {
        return $this->meta_title ?: $this->title;
    }

    public function metaDescription(): string
    {
        return $this->meta_description ?: (string) $this->excerpt;
    }
}
