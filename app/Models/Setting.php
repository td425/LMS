<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    public static function getValue(string $key, ?string $default = null): ?string
    {
        if (! static::tableReady()) {
            return $default;
        }

        return Cache::rememberForever('setting.'.$key, function () use ($key, $default) {
            $setting = static::query()->where('key', $key)->first();

            return $setting?->value ?? $default;
        });
    }

    public static function setValue(string $key, ?string $value): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value],
        );

        Cache::forget('setting.'.$key);
    }

    public static function logoUrl(): string
    {
        $configured = static::getValue('site_logo');

        if (filled($configured)) {
            if (str_starts_with($configured, 'http://') || str_starts_with($configured, 'https://')) {
                return $configured;
            }

            return asset(ltrim($configured, '/'));
        }

        return asset('images/logo-new.png');
    }

    public static function siteName(): string
    {
        return static::getValue('site_name', config('app.name', 'LearnHost')) ?: config('app.name', 'LearnHost');
    }

    protected static function tableReady(): bool
    {
        try {
            return Schema::hasTable('settings');
        } catch (\Throwable) {
            return false;
        }
    }
}
