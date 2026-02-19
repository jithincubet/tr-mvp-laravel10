<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * Currency Model
 * 
 * Represents a user's endorsement record/qualification status.
 * Tracks qualification dates, expiry, and status.
 * 
 * @property int $id
 * @property int $client_id
 * @property int $user_id
 * @property int $endorsement_id
 * @property int|null $session_id
 * @property string|null $status
 * @property string|null $date_qualified
 * @property string|null $date_expired
 * @property bool $current
 * @property bool $passes
 * @property bool $fails
 * @property bool $disabled
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class Currency extends Model
{
    use SoftDeletes;

    protected $table = 'tr2_ems_currencies';

    protected $fillable = [
        'client_id',
        'user_id',
        'endorsement_id',
        'session_id',
        'status',
        'date_qualified',
        'date_expired',
        'description',
        'notes',
        'reference',
        'current',
        'passes',
        'fails',
        'disabled',
        'progress',
        'score',
        'started',
        'ended',
        'checked',
        'changed',
        'expects',
        'schedules',
        'ltc_base',
        'ltc_authorization_level',
        'expiring_reminders_sent',
        'last_reminder_sent_at',
    ];

    protected $casts = [
        'date_qualified' => 'date',
        'date_expired' => 'date',
        'current' => 'boolean',
        'passes' => 'boolean',
        'fails' => 'boolean',
        'disabled' => 'boolean',
        'progress' => 'integer',
        'score' => 'integer',
        'started' => 'datetime',
        'ended' => 'datetime',
        'checked' => 'datetime',
        'changed' => 'datetime',
        'expiring_reminders_sent' => 'integer',
        'last_reminder_sent_at' => 'datetime',
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

    public function endorsement(): BelongsTo
    {
        return $this->belongsTo(Endorsement::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(EventSession::class, 'session_id');
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

    public function scopeCurrent(Builder $query): Builder
    {
        return $query->where('current', true);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('disabled', false);
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('date_expired', '<', now()->toDateString());
    }

    public function scopeValid(Builder $query): Builder
    {
        return $query->where('date_expired', '>=', now()->toDateString())
            ->orWhereNull('date_expired');
    }

    public function scopeExpiringSoon(Builder $query, int $days = 30): Builder
    {
        return $query->whereBetween('date_expired', [
            now()->toDateString(),
            now()->addDays($days)->toDateString(),
        ]);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    // Accessors

    public function getIsExpiredAttribute(): bool
    {
        return $this->date_expired && $this->date_expired < now()->toDateString();
    }

    public function getIsValidAttribute(): bool
    {
        return !$this->is_expired && !$this->disabled && !$this->deleted_at;
    }
}
