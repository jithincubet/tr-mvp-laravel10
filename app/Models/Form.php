<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

/**
 * Form Model
 * 
 * Represents a training form template with blocks and elements.
 * 
 * @property int $id
 * @property int $client_id
 * @property string $name
 * @property string|null $description
 * @property string|null $event_type
 * @property bool $enabled
 * @property int|null $required_sectors
 * @property array|null $release_criteria
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Form extends Model
{
    protected $table = 'tr2_forms';

    protected $fillable = [
        'client_id',
        'name',
        'description',
        'event_type',
        'enabled',
        'required_sectors',
        'release_criteria',
        'requires_ltc_recommendation',
        'auto_archive',
        'exposure_min_takeoffs',
        'exposure_min_landings',
        'exposure_min_sectors',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'requires_ltc_recommendation' => 'boolean',
        'auto_archive' => 'boolean',
        'required_sectors' => 'integer',
        'exposure_min_takeoffs' => 'integer',
        'exposure_min_landings' => 'integer',
        'exposure_min_sectors' => 'integer',
        'release_criteria' => 'array',
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

    public function blocks(): HasMany
    {
        return $this->hasMany(Block::class)->orderBy('sortorder');
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function formEndorsements(): HasMany
    {
        return $this->hasMany(FormEndorsement::class);
    }

    public function endorsements()
    {
        return $this->belongsToMany(Endorsement::class, 'tr2_form_endorsements', 'form_id', 'endorsement_id');
    }

    public function instructorEndorsements(): HasMany
    {
        return $this->hasMany(FormInstructorEndorsement::class);
    }

    public function exposureRequirements(): HasMany
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

    public function scopeByEventType(Builder $query, string $eventType): Builder
    {
        return $query->where('event_type', $eventType);
    }
}
