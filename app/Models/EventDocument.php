<?php

namespace App\Models;

use Database\Factories\EventDocumentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'event_id',
    'file_path',
    'file_type',
    'caption',
    'is_public',
    'uploaded_by',
])]
class EventDocument extends Model
{
    /** @use HasFactory<EventDocumentFactory> */
    use HasFactory;

    protected $casts = [
        'is_public' => 'boolean',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
