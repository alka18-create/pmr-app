<?php

namespace App\Models;

use App\Enums\CertificateSourceType;
use Database\Factories\CertificateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable([
    'code',
    'member_id',
    'source_type',
    'source_id',
    'title',
    'issued_at',
    'file_path',
    'is_revoked',
    'created_by',
])]
class Certificate extends Model
{
    /** @use HasFactory<CertificateFactory> */
    use HasFactory;

    protected $casts = [
        'source_type' => CertificateSourceType::class,
        'issued_at' => 'date',
        'is_revoked' => 'boolean',
    ];

    public static function generateUniqueCode(): string
    {
        do {
            $code = strtoupper(Str::random(16).'-'.Str::random(15));
        } while (static::where('code', $code)->exists());

        return substr($code, 0, 32);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isValid(): bool
    {
        return ! $this->is_revoked;
    }
}
