<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

/**
 * Event Model
 * 
 * Represents a training event (classroom, simulator, line training, etc.)
 * 
 * @property int $id
 * @property int $client_id
 * @property string $name
 * @property string $date
 * @property int|null $form_id
 * @property int|null $instructor_id
 * @property int|null $facility_id
 * @property string $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Event extends Model
{
    protected $table = 'tr2_events';

    protected $fillable = [
        'client_id',
        'name',
        'date',
        'form_id',
        'instructor_id',
        'facility_id',
        'status',
        'instructor_comment',
        'notes',
        'start_time',
        'end_time',
        'location',
        'max_participants',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'max_participants' => 'integer',
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

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(TrainingFacility::class, 'facility_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(EventSession::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(EventParticipant::class);
    }

    public function eventEndorsements(): HasMany
    {
        return $this->hasMany(EventEndorsement::class);
    }

    public function endorsements()
    {
        return $this->belongsToMany(Endorsement::class, 'tr2_event_endorsements', 'event_id', 'endorsement_id');
    }

    // Scopes

    public function scopeForClient(Builder $query, int $clientId): Builder
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('date', '>=', now()->toDateString());
    }

    public function scopePast(Builder $query): Builder
    {
        return $query->where('date', '<', now()->toDateString());
    }
}
