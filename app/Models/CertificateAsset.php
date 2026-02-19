<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * CertificateAsset Model
 * 
 * Stores images and assets for certificate templates.
 * 
 * @property int $id
 * @property int $client_id
 * @property string $name
 * @property string $file_path
 * @property string $file_type
 * @property int|null $file_size
 * @property \Carbon\Carbon $created_at
 */
class CertificateAsset extends Model
{
    protected $table = 'tr2_certificate_assets';

    public $timestamps = false;

    protected $fillable = [
        'client_id',
        'name',
        'file_path',
        'file_type',
        'file_size',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'created_at' => 'datetime',
    ];

    // Global Scope for Multi-Tenancy
    protected static function booted()
    {
        static::addGlobalScope('client', function (Builder $query) {
            if (auth()->check()) {
                $query->where('client_id', auth()->user()->client_id);
            }
        });
    }

    // Relationships

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    // Scopes

    public function scopeForClient(Builder $query, int $clientId): Builder
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('file_type', $type);
    }

    // Accessors

    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size ?? 0;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
