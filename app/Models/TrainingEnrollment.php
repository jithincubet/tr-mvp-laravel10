<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * TrainingEnrollment Model
 * 
 * Represents a user's enrollment in a training program.
 * Uses polymorphic relationship for enrollable (Training, Event, etc.)
 * 
 * @property int $id
 * @property int $client_id
 * @property int $user_id
 * @property string $enrollable_type
 * @property int $enrollable_id
 * @property string $status
 * @property \Carbon\Carbon|null $enrolled_at
 * @property \Carbon\Carbon|null $completed_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class TrainingEnrollment extends Model
{
    use SoftDeletes;

    protected $table = 'tr2_training_enrollments';

    protected $fillable = [
        'client_id',
        'user_id',
        'enrollable_type',
        'enrollable_id',
        'status',
        'enrolled_at',
        'started_at',
        'completed_at',
        'due_date',
        'progress',
        'score',
        'attempts',
        'notes',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'due_date' => 'date',
        'progress' => 'integer',
        'score' => 'integer',
        'attempts' => 'integer',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function enrollable(): MorphTo
    {
        return $this->morphTo();
    }

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class, 'enrollable_id')
            ->where('enrollable_type', Training::class);
    }

    // Scopes

    public function scopeForClient(Builder $query, int $clientId): Builder
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->whereNotNull('completed_at');
    }

    public function scopeInProgress(Builder $query): Builder
    {
        return $query->whereNotNull('started_at')->whereNull('completed_at');
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('due_date', '<', now()->toDateString())
            ->whereNull('completed_at');
    }
}
