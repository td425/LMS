<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class Setting extends Model
{
    protected $fillable = [
        'name',
        'content',
    ];

    public static function getValue(string $name, ?string $default = null): ?string
    {
        if (! static::tableReady()) {
            return $default;
        }

        try {
            return Cache::store('file')->remember('setting.'.$name, 3600, function () use ($name, $default) {
                $setting = static::query()->where('name', $name)->first();

                return $setting?->content ?? $default;
            });
        } catch (\Throwable) {
            try {
                $setting = static::query()->where('name', $name)->first();

                return $setting?->content ?? $default;
            } catch (\Throwable) {
                return $default;
            }
        }
    }

    public static function setValue(string $name, ?string $value): void
    {
        static::query()->updateOrCreate(
            ['name' => $name],
            ['content' => $value],
        );

        try {
            Cache::store('file')->forget('setting.'.$name);
        } catch (\Throwable) {
            // Ignore cache failures on shared hosting.
        }
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
