<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

/**
 * ExposureType Model
 * 
 * Defines operational exposure types for line training.
 * E.g., Night Landings, CAT II/III Approaches, Crosswind Landings
 * 
 * @property int $id
 * @property int $client_id
 * @property string $name
 * @property string|null $code
 * @property string|null $description
 * @property string|null $category
 * @property bool $enabled
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ExposureType extends Model
{
    protected $table = 'tr2_exposure_types';

    protected $fillable = [
        'client_id',
        'name',
        'code',
        'description',
        'category',
        'enabled',
    ];

    protected $casts = [
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

    public function sessionExposures(): HasMany
    {
        return $this->hasMany(SessionExposure::class);
    }

    public function formRequirements(): HasMany
    {
        return $this->hasMany(FormExposureRequirement::class);
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

    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }
}
