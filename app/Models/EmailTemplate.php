<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

/**
 * EmailTemplate Model
 * 
 * Defines email templates for notifications and reports.
 * 
 * @property int $id
 * @property int|null $client_id
 * @property string $name
 * @property string $notification_type
 * @property string $subject
 * @property string $body_html
 * @property bool $enabled
 * @property bool $is_default
 * @property array|null $table_config
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class EmailTemplate extends Model
{
    protected $table = 'tr2_email_templates';

    protected $fillable = [
        'client_id',
        'name',
        'notification_type',
        'subject',
        'body_html',
        'enabled',
        'is_default',
        'table_config',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'is_default' => 'boolean',
        'table_config' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Global Scope for Multi-Tenancy
    protected static function booted()
    {
        static::addGlobalScope('client', function (Builder $query) {
            if (auth()->check()) {
                $query->where(function ($q) {
                    $q->where('client_id', auth()->user()->client_id)
                      ->orWhereNull('client_id'); // Include global templates
                });
            }
        });
    }

    // Relationships

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function emailLogs(): HasMany
    {
        return $this->hasMany(EmailLog::class, 'template_id');
    }

    public function notificationRulesAsLearner(): HasMany
    {
        return $this->hasMany(NotificationRule::class, 'learner_template_id');
    }

    public function notificationRulesAsAdmin(): HasMany
    {
        return $this->hasMany(NotificationRule::class, 'admin_template_id');
    }

    // Scopes

    public function scopeForClient(Builder $query, int $clientId): Builder
    {
        return $query->where(function ($q) use ($clientId) {
            $q->where('client_id', $clientId)
              ->orWhereNull('client_id');
        });
    }

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('enabled', true);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('notification_type', $type);
    }

    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    public function scopeGlobal(Builder $query): Builder
    {
        return $query->whereNull('client_id');
    }
}
