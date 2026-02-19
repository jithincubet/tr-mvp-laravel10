<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

/**
 * EndorsementForm Model
 * 
 * Defines custom data collection forms for endorsements.
 * Contains fields for learner/admin data entry.
 * 
 * @property int $id
 * @property int $client_id
 * @property string $name
 * @property string|null $description
 * @property string|null $reference
 * @property bool $disabled
 * @property int $sort_order
 * @property array|null $properties
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class EndorsementForm extends Model
{
    protected $table = 'tr2_endorsement_forms';

    protected $fillable = [
        'client_id',
        'name',
        'description',
        'reference',
        'disabled',
        'sort_order',
        'properties',
    ];

    protected $casts = [
        'disabled' => 'boolean',
        'sort_order' => 'integer',
        'properties' => 'array',
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

    public function fields(): HasMany
    {
        return $this->hasMany(EndorsementFormField::class, 'form_id')->orderBy('sort_order');
    }

    public function endorsements(): HasMany
    {
        return $this->hasMany(Endorsement::class, 'form_id');
    }

    // Scopes

    public function scopeForClient(Builder $query, int $clientId): Builder
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('disabled', false);
    }
}
