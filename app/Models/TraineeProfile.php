<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * TraineeProfile Model
 * 
 * Stores trainee-specific data for line training/IOE tracking.
 * 
 * @property int $id
 * @property int $client_id
 * @property int $user_id
 * @property int|null $aircraft_type_id
 * @property string $training_status
 * @property int $sectors_completed
 * @property string|null $current_phase
 * @property \Carbon\Carbon|null $training_started_at
 * @property \Carbon\Carbon|null $training_completed_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class TraineeProfile extends Model
{
    protected $table = 'tr2_trainee_profiles';

    protected $fillable = [
        'client_id',
        'user_id',
        'aircraft_type_id',
        'training_status',
        'sectors_completed',
        'current_phase',
        'training_started_at',
        'training_completed_at',
        'notes',
        'base_airport',
        'instructor_notes',
    ];

    protected $casts = [
        'sectors_completed' => 'integer',
        'training_started_at' => 'datetime',
        'training_completed_at' => 'datetime',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function aircraftType(): BelongsTo
    {
        return $this->belongsTo(AircraftType::class);
    }

    // Scopes

    public function scopeForClient(Builder $query, int $clientId): Builder
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('training_status', $status);
    }

    public function scopeInTraining(Builder $query): Builder
    {
        return $query->whereIn('training_status', ['in_ioe', 'in_training']);
    }

    public function scopeReadyForRelease(Builder $query): Builder
    {
        return $query->where('training_status', 'ready_for_line_check');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('training_status', 'released');
    }
}
