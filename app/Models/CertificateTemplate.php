<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

/**
 * CertificateTemplate Model
 * 
 * Defines certificate templates with canvas data for PDF generation.
 * 
 * @property int $id
 * @property int $client_id
 * @property string $name
 * @property string|null $description
 * @property string $page_size
 * @property string $page_orientation
 * @property string|null $background_image
 * @property array|null $canvas_data
 * @property bool $enabled
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class CertificateTemplate extends Model
{
    protected $table = 'tr2_certificate_templates';

    protected $fillable = [
        'client_id',
        'name',
        'description',
        'page_size',
        'page_orientation',
        'background_image',
        'canvas_data',
        'enabled',
    ];

    protected $casts = [
        'canvas_data' => 'array',
        'enabled' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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

    public function assets(): HasMany
    {
        return $this->hasMany(CertificateAsset::class, 'client_id', 'client_id');
    }

    // Scopes

    public function scopeForClient(Builder $query, int $clientId): Builder
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('enabled', true);
    }

    public function scopeByPageSize(Builder $query, string $size): Builder
    {
        return $query->where('page_size', $size);
    }
}
