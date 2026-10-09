<?php

namespace App\Models;

use App\Enums\MaterialCategory;
use App\Enums\MaterialLevel;
use Database\Factories\MaterialFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable([
    'title',
    'slug',
    'category',
    'content',
    'file_path',
    'level',
    'order',
    'is_public',
    'uploaded_by',
])]
class Material extends Model
{
    /** @use HasFactory<MaterialFactory> */
    use HasFactory;

    protected $casts = [
        'category' => MaterialCategory::class,
        'level' => MaterialLevel::class,
        'is_public' => 'boolean',
        'order' => 'integer',
    ];

    public static function booted(): void
    {
        static::creating(function (Material $material) {
            if (blank($material->slug)) {
                $material->slug = static::generateUniqueSlug($material->title);
            }
        });
    }

    public static function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $slug = $base !== '' ? $base : 'materi';
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

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }
}
