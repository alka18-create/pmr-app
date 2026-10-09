<?php

namespace App\Models;

use App\Enums\GalleryFileType;
use Database\Factories\GalleryItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'category_id',
    'title',
    'description',
    'file_path',
    'file_type',
    'video_url',
    'event_id',
    'is_public',
    'uploaded_by',
    'taken_at',
])]
class GalleryItem extends Model
{
    /** @use HasFactory<GalleryItemFactory> */
    use HasFactory;

    protected $casts = [
        'file_type' => GalleryFileType::class,
        'is_public' => 'boolean',
        'taken_at' => 'date',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(GalleryCategory::class, 'category_id');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function isPubliclyVisible(): bool
    {
        return $this->is_public;
    }
}
