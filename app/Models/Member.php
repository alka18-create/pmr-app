<?php

namespace App\Models;

use App\Enums\Gender;
use App\Enums\MemberStatus;
use Database\Factories\MemberFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'user_id',
    'nis',
    'nisn',
    'full_name',
    'slug',
    'gender',
    'birth_date',
    'birth_place',
    'address',
    'phone',
    'email_pribadi',
    'parent_name',
    'parent_phone',
    'blood_type',
    'batch_id',
    'class_id',
    'position_id',
    'photo_path',
    'status',
    'joined_at',
    'notes',
])]
class Member extends Model
{
    /** @use HasFactory<MemberFactory> */
    use HasFactory, SoftDeletes;

    protected $casts = [
        'gender' => Gender::class,
        'status' => MemberStatus::class,
        'birth_date' => 'date',
        'joined_at' => 'date',
    ];

    public static function booted(): void
    {
        static::creating(function (Member $member) {
            if (blank($member->slug)) {
                $member->slug = static::generateUniqueSlug($member->full_name);
            }
        });
    }

    public static function generateUniqueSlug(string $fullName, ?int $ignoreId = null): string
    {
        $base = Str::slug($fullName);
        $slug = $base !== '' ? $base : 'anggota';
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

    public static function publicFields(): array
    {
        return [
            'id',
            'full_name',
            'slug',
            'gender',
            'photo_path',
            'status',
            'joined_at',
        ];
    }

    public static function internalFields(): array
    {
        return [
            'nis',
            'nisn',
            'birth_date',
            'birth_place',
            'address',
            'phone',
            'email_pribadi',
            'parent_name',
            'parent_phone',
            'blood_type',
            'notes',
            'user_id',
        ];
    }

    public function toPublicArray(): array
    {
        $data = [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'slug' => $this->slug,
            'gender' => $this->gender?->value,
            'photo_path' => $this->photo_path,
            'status' => $this->status?->value,
            'joined_at' => $this->joined_at?->toDateString(),
        ];

        if ($this->relationLoaded('batch')) {
            $data['batch'] = [
                'id' => $this->batch?->id,
                'name' => $this->batch?->name,
                'year' => $this->batch?->year,
            ];
        }

        if ($this->relationLoaded('schoolClass')) {
            $data['school_class'] = [
                'id' => $this->schoolClass?->id,
                'name' => $this->schoolClass?->name,
                'level' => $this->schoolClass?->level,
            ];
        }

        if ($this->relationLoaded('position')) {
            $data['position'] = [
                'id' => $this->position?->id,
                'name' => $this->position?->name,
            ];
        }

        return $data;
    }

    /**
     * Status yang boleh tampil di halaman publik (disaring setting).
     *
     * @return list<string>
     */
    public static function publiclyVisibleStatusValues(): array
    {
        $statuses = [MemberStatus::Aktif->value];

        if (filter_var(Setting::get('public_show_alumni', '0'), FILTER_VALIDATE_BOOLEAN)) {
            $statuses[] = MemberStatus::Alumni->value;
        }

        if (filter_var(Setting::get('public_show_nonaktif', '0'), FILTER_VALIDATE_BOOLEAN)) {
            $statuses[] = MemberStatus::Nonaktif->value;
        }

        return $statuses;
    }

    public function isPublicProfile(): bool
    {
        return in_array($this->status?->value, static::publiclyVisibleStatusValues(), true);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function positionHistory(): BelongsToMany
    {
        return $this->belongsToMany(Position::class, 'member_position')
            ->withPivot(['start_date', 'end_date'])
            ->withTimestamps();
    }

    public function positionHistoryEntries(): HasMany
    {
        return $this->hasMany(MemberPosition::class);
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }
}
