<?php

namespace App\Models;

use App\Enums\QuizAttemptStatus;
use Database\Factories\QuizAttemptFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'quiz_id',
    'member_id',
    'attempt_no',
    'started_at',
    'finished_at',
    'score',
    'status',
    'ip',
])]
class QuizAttempt extends Model
{
    /** @use HasFactory<QuizAttemptFactory> */
    use HasFactory;

    protected $casts = [
        'attempt_no' => 'integer',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'score' => 'integer',
        'status' => QuizAttemptStatus::class,
    ];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuizAnswer::class, 'attempt_id');
    }

    public function isExpired(): bool
    {
        if ($this->status !== QuizAttemptStatus::Running) {
            return false;
        }

        if ($this->quiz?->time_limit_minutes === null) {
            return false;
        }

        return $this->started_at
            ->copy()
            ->addMinutes($this->quiz->time_limit_minutes)
            ->isPast();
    }

    public function secondsRemaining(): ?int
    {
        if ($this->quiz?->time_limit_minutes === null || $this->status !== QuizAttemptStatus::Running) {
            return null;
        }

        $deadline = $this->started_at->copy()->addMinutes($this->quiz->time_limit_minutes);
        $remaining = now()->diffInSeconds($deadline, false);

        return max(0, (int) $remaining);
    }
}
