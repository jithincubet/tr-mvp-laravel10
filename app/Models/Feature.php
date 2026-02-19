<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

/**
 * Feature Model
 * 
 * Defines application features for RBAC.
 * Features are assigned to roles via RoleFeature.
 * 
 * @property int $id
 * @property int $client_id
 * @property string $name
 * @property string $code
 * @property string|null $route
 * @property string|null $section
 * @property string|null $description
 * @property bool $enabled
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Feature extends Model
{
    protected $table = 'tr2_features';

    protected $fillable = [
        'client_id',
        'name',
        'code',
        'route',
        'section',
        'description',
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

    public function roleFeatures(): HasMany
    {
        return $this->hasMany(RoleFeature::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'tr2_role_features', 'feature_id', 'role_id');
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

    public function scopeBySection(Builder $query, string $section): Builder
    {
        return $query->where('section', $section);
    }
}
