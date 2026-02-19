<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

/**
 * TrainingFacility Model
 * 
 * Represents training locations and devices (simulators, classrooms, etc.)
 * 
 * @property int $id
 * @property int $client_id
 * @property int|null $type_id
 * @property string $name
 * @property string|null $description
 * @property string|null $location
 * @property bool $enabled
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class TrainingFacility extends Model
{
    protected $table = 'tr2_training_facilities';

    protected $fillable = [
        'client_id',
        'type_id',
        'name',
        'description',
        'location',
        'address',
        'city',
        'country',
        'capacity',
        'enabled',
        'notes',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'capacity' => 'integer',
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

    public function type(): BelongsTo
    {
        return $this->belongsTo(FacilityType::class, 'type_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'facility_id');
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

    public function scopeByType(Builder $query, int $typeId): Builder
    {
        return $query->where('type_id', $typeId);
    }
}
