<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

/**
 * EventSession Model
 * 
 * Represents a trainee's session within an event.
 * Contains grading results, notes, and approval status.
 * 
 * @property int $id
 * @property int $client_id
 * @property int $event_id
 * @property int $user_id
 * @property int|null $instructor_id
 * @property string $status
 * @property string|null $pass_fail_result
 * @property bool|null $is_approved
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class EventSession extends Model
{
    protected $table = 'tr2_event_sessions';

    protected $fillable = [
        'client_id',
        'event_id',
        'user_id',
        'instructor_id',
        'status',
        'session_number',
        'session_date',
        'pass_fail_result',
        'is_approved',
        'approved_at',
        'approved_by_user_id',
        'archived_at',
        'archive_reason',
        'notes_public',
        'notes_private',
        'notes_admin',
        'signature_data',
        'requires_admin_attention',
        'admin_attention_reason',
        'termination_reason',
        'termination_notes',
        'crm_assessment',
        'ltc_recommendation',
        'ltc_recommendation_notes',
        'is_line_check',
        'flight_route',
        'flight_number',
        'aircraft_registration',
        'sector_type',
        'endorsement_ids',
    ];

    protected $casts = [
        'session_date' => 'date',
        'is_approved' => 'boolean',
        'approved_at' => 'datetime',
        'archived_at' => 'datetime',
        'requires_admin_attention' => 'boolean',
        'is_line_check' => 'boolean',
        'endorsement_ids' => 'array',
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

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function grades(): HasMany
    {
        return $this->hasMany(EventSessionGrade::class, 'session_id');
    }

    public function sectors(): HasMany
    {
        return $this->hasMany(SessionSector::class, 'session_id');
    }

    public function exposures(): HasMany
    {
        return $this->hasMany(SessionExposure::class, 'session_id');
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

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('is_approved', true);
    }

    public function scopePendingApproval(Builder $query): Builder
    {
        return $query->whereNull('is_approved')->orWhere('is_approved', false);
    }

    public function scopeRequiresAttention(Builder $query): Builder
    {
        return $query->where('requires_admin_attention', true);
    }
}
