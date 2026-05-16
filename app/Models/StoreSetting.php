<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{
    protected $fillable = ['key', 'value', 'label', 'type', 'description'];

    /**
     * Get a setting value by key, with optional default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value by key.
     */
    public static function set(string $key, mixed $value): void
    {
        static::where('key', $key)->update(['value' => $value]);
    }

    /**
     * Get all settings as a key => value array.
     */
    public static function all($columns = ['*']): \Illuminate\Database\Eloquent\Collection
    {
        return static::query()->get($columns);
    }

    /**
     * Get all settings as associative array key => value.
     */
    public static function asArray(): array
    {
        return static::query()->pluck('value', 'key')->toArray();
    }
}
