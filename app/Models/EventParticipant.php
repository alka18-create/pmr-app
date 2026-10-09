<?php

namespace App\Models;

use App\Enums\ParticipantRole;
use Database\Factories\EventParticipantFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

#[Fillable([
    'event_id',
    'member_id',
    'role_in_event',
    'qr_token',
    'invited_at',
])]
class EventParticipant extends Model
{
    /** @use HasFactory<EventParticipantFactory> */
    use HasFactory;

    protected $casts = [
        'role_in_event' => ParticipantRole::class,
        'invited_at' => 'datetime',
    ];

    public static function booted(): void
    {
        static::creating(function (EventParticipant $participant) {
            if (blank($participant->qr_token)) {
                $participant->qr_token = (string) Str::uuid();
            }
        });
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function attendanceRecord(): HasOne
    {
        return $this->hasOne(AttendanceRecord::class, 'member_id', 'member_id')
            ->where('event_id', $this->event_id ?? $this->getAttribute('event_id'));
    }
}
