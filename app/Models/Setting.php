<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

#[Fillable(['key', 'value', 'group'])]
class Setting extends Model
{
    public static function cacheKey(string $key): string
    {
        return "setting.{$key}";
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $entry = Cache::rememberForever(
            self::cacheKey($key),
            function () use ($key) {
                $setting = static::query()->where('key', $key)->first();

                return [
                    'found' => $setting !== null,
                    'value' => $setting?->value,
                ];
            },
        );

        return $entry['found'] ? $entry['value'] : $default;
    }

    public static function set(string $key, mixed $value, string $group = 'general'): static
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value === null ? null : (string) $value, 'group' => $group],
        );
    }

    protected static function booted(): void
    {
        static::saved(function (Setting $setting) {
            Cache::forget(self::cacheKey($setting->key));
        });

        static::deleted(function (Setting $setting) {
            Cache::forget(self::cacheKey($setting->key));
        });
    }
}
