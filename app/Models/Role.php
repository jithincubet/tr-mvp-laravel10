<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

/**
 * Role Model
 * 
 * Defines custom role definitions for RBAC.
 * Links to features via RoleFeature pivot.
 * 
 * @property int $id
 * @property int $client_id
 * @property string $name
 * @property string $code
 * @property string|null $description
 * @property bool $is_default
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Role extends Model
{
    protected $table = 'tr2_roles';

    protected $fillable = [
        'client_id',
        'name',
        'code',
        'description',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
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

    public function roleFeatures(): HasMany
    {
        return $this->hasMany(RoleFeature::class);
    }

    public function features()
    {
        return $this->belongsToMany(Feature::class, 'tr2_role_features', 'role_id', 'feature_id');
    }

    // Scopes

    public function scopeForClient(Builder $query, int $clientId): Builder
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }
}
