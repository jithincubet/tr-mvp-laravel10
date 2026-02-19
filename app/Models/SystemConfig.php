<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * SystemConfig Model
 * 
 * Stores system-wide configuration values.
 * Not scoped to client - global settings.
 * 
 * @property int $id
 * @property string $key
 * @property string|null $value
 * @property string|null $description
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class SystemConfig extends Model
{
    protected $table = 'tr2_system_config';

    protected $fillable = [
        'key',
        'value',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Scopes

    public function scopeByKey($query, string $key)
    {
        return $query->where('key', $key);
    }

    // Static Methods

    public static function getValue(string $key, $default = null)
    {
        $config = self::where('key', $key)->first();
        return $config ? $config->value : $default;
    }

    public static function setValue(string $key, string $value, ?string $description = null): self
    {
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'description' => $description]
        );
    }
}
