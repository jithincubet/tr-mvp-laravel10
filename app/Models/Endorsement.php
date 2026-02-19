<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

/**
 * Endorsement Model
 * 
 * Represents a qualification/certification type.
 * 
 * @property int $id
 * @property int $client_id
 * @property string $name
 * @property string $code
 * @property int|null $type_id
 * @property int|null $schedule_id
 * @property int|null $form_id
 * @property bool $disabled
 * @property bool $enrollment
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class Endorsement extends Model
{
    use SoftDeletes;

    protected $table = 'tr2_endorsements';

    protected $fillable = [
        'client_id',
        'name',
        'code',
        'description',
        'reference',
        'type_id',
        'schedule_id',
        'form_id',
        'training_id',
        'notification_id',
        'disabled',
        'enrollment',
    ];

    protected $casts = [
        'disabled' => 'boolean',
        'enrollment' => 'boolean',
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

    public function type(): BelongsTo
    {
        return $this->belongsTo(EndorsementType::class, 'type_id');
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(EndorsementSchedule::class, 'schedule_id');
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(EndorsementForm::class, 'form_id');
    }

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class);
    }

    public function currencies(): HasMany
    {
        return $this->hasMany(Currency::class);
    }

    public function eventEndorsements(): HasMany
    {
        return $this->hasMany(EventEndorsement::class);
    }

    public function formEndorsements(): HasMany
    {
        return $this->hasMany(FormEndorsement::class);
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

    public function scopeWithEnrollment(Builder $query): Builder
    {
        return $query->where('enrollment', true);
    }
}
