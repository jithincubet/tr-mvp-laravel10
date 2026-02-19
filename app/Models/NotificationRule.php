<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

/**
 * NotificationRule Model
 * 
 * Defines rules for automatic notification triggering.
 * 
 * @property int $id
 * @property int $client_id
 * @property string $name
 * @property string $type
 * @property string $frequency
 * @property string|null $status
 * @property bool $enabled
 * @property bool $notify_learner
 * @property array|null $notify_admin_ids
 * @property int|null $learner_template_id
 * @property int|null $admin_template_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class NotificationRule extends Model
{
    protected $table = 'tr2_notification_rules';

    protected $fillable = [
        'client_id',
        'name',
        'type',
        'frequency',
        'status',
        'endorsement_filter',
        'endorsement_ids',
        'team_ids',
        'training_ids',
        'days_threshold',
        'session_trigger',
        'enabled',
        'notify_learner',
        'notify_admin_ids',
        'learner_template_id',
        'admin_template_id',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'notify_learner' => 'boolean',
        'endorsement_ids' => 'array',
        'team_ids' => 'array',
        'training_ids' => 'array',
        'notify_admin_ids' => 'array',
        'days_threshold' => 'integer',
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

    public function learnerTemplate(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'learner_template_id');
    }

    public function adminTemplate(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'admin_template_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'rule_id');
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

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeByFrequency(Builder $query, string $frequency): Builder
    {
        return $query->where('frequency', $frequency);
    }
}
