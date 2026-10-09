<?php

namespace App\Models;

use App\Enums\AchievementCategory;
use App\Enums\AchievementLevel;
use Database\Factories\AchievementFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'title',
    'level',
    'category',
    'year',
    'description',
    'attachment_path',
    'winner',
    'is_public',
])]
class Achievement extends Model
{
    /** @use HasFactory<AchievementFactory> */
    use HasFactory;

    protected $casts = [
        'level' => AchievementLevel::class,
        'category' => AchievementCategory::class,
        'year' => 'integer',
        'is_public' => 'boolean',
    ];

    public function isPubliclyVisible(): bool
    {
        return $this->is_public;
    }
}
