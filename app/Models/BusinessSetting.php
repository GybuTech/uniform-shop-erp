<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class BusinessSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Retrieve a setting by key with fallback default.
     */
    public static function get(string $key, $default = null)
    {
        return Cache::remember("business_setting_{$key}", 3600, function () use ($key, $default) {
            $record = static::where('key', $key)->first();
            return $record !== null && $record->value !== null ? $record->value : $default;
        });
    }

    /**
     * Store or update a setting by key.
     */
    public static function set(string $key, $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
        Cache::forget("business_setting_{$key}");
    }
}
