<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

/**
 * EndorsementType Model
 * 
 * Categorizes endorsements (e.g., Type Rating, Currency, License).
 * 
 * @property int $id
 * @property int $client_id
 * @property string $name
 * @property string|null $description
 * @property string|null $reference
 * @property string $scheme
 * @property bool $is_default
 * @property bool $learner_upload
 * @property bool $disabled
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class EndorsementType extends Model
{
    protected $table = 'tr2_endorsement_types';

    protected $fillable = [
        'client_id',
        'name',
        'description',
        'reference',
        'scheme',
        'is_default',
        'learner_upload',
        'disabled',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'learner_upload' => 'boolean',
        'disabled' => 'boolean',
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

    public function endorsements(): HasMany
    {
        return $this->hasMany(Endorsement::class, 'type_id');
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

    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }
}
