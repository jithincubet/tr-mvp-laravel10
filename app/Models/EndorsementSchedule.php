<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

/**
 * EndorsementSchedule Model
 * 
 * Defines recurrency/validity schedules for endorsements.
 * 
 * @property int $id
 * @property int $client_id
 * @property string|null $name
 * @property string|null $description
 * @property int $expire_months
 * @property int $check_days
 * @property int $open_days
 * @property bool $once
 * @property bool $resume
 * @property bool $is_default
 * @property bool $disabled
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class EndorsementSchedule extends Model
{
    use SoftDeletes;

    protected $table = 'tr2_endorsement_schedules';

    protected $fillable = [
        'client_id',
        'name',
        'description',
        'reference',
        'expire_months',
        'check_days',
        'open_days',
        'cycles',
        'once',
        'resume',
        'is_default',
        'disabled',
        'properties',
    ];

    protected $casts = [
        'expire_months' => 'integer',
        'check_days' => 'integer',
        'open_days' => 'integer',
        'once' => 'boolean',
        'resume' => 'boolean',
        'is_default' => 'boolean',
        'disabled' => 'boolean',
        'properties' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
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
        return $this->hasMany(Endorsement::class, 'schedule_id');
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
