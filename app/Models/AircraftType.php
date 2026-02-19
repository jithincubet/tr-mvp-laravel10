<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

/**
 * AircraftType Model
 * 
 * Defines aircraft types for fleet management and training.
 * 
 * @property int $id
 * @property int $client_id
 * @property string $code
 * @property string $name
 * @property string|null $category
 * @property \Carbon\Carbon $created_at
 */
class AircraftType extends Model
{
    protected $table = 'tr2_aircraft_types';

    public $timestamps = false;

    protected $fillable = [
        'client_id',
        'code',
        'name',
        'category',
    ];

    protected $casts = [
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

    public function traineeProfiles(): HasMany
    {
        return $this->hasMany(TraineeProfile::class);
    }

    // Scopes

    public function scopeForClient(Builder $query, int $clientId): Builder
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }
}
