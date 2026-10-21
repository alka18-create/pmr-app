<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    private const SENSITIVE_KEYS = [
        'password',
        'remember_token',
        'token',
        'qr_token',
        'nisn',
        'nis',
    ];

    /**
     * @param  array<string, mixed>  $old
     * @param  array<string, mixed>  $new
     */
    public function log(
        string $action,
        ?Model $model = null,
        array $old = [],
        array $new = [],
        ?User $user = null,
    ): AuditLog {
        $user ??= Auth::user();

        return AuditLog::create([
            'user_id' => $user?->getKey(),
            'action' => $action,
            'model_type' => $model?->getMorphClass(),
            'model_id' => $model?->getKey(),
            'old_values' => self::filter($old),
            'new_values' => self::filter($new),
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    public static function filter(array $values): array
    {
        $result = [];

        foreach ($values as $key => $value) {
            if (is_string($key) && (
                in_array($key, self::SENSITIVE_KEYS, true)
                || ($key !== 'password_changed' && str_contains($key, 'password'))
            )) {
                continue;
            }

            if ($value instanceof \BackedEnum) {
                $value = $value->value;
            }

            $result[$key] = $value;
        }

        return $result;
    }
}
