<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * ScheduledReport Model
 * 
 * Defines scheduled/automated report configurations.
 * 
 * @property int $id
 * @property int $client_id
 * @property string $name
 * @property string $report_type
 * @property string $frequency
 * @property int|null $template_id
 * @property array|null $recipients
 * @property array|null $filters
 * @property bool $enabled
 * @property \Carbon\Carbon|null $last_run_at
 * @property \Carbon\Carbon|null $next_run_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ScheduledReport extends Model
{
    protected $table = 'tr2_scheduled_reports';

    protected $fillable = [
        'client_id',
        'name',
        'report_type',
        'frequency',
        'template_id',
        'recipients',
        'filters',
        'enabled',
        'last_run_at',
        'next_run_at',
        'schedule_time',
        'schedule_day',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'recipients' => 'array',
        'filters' => 'array',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
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

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'template_id');
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

    public function scopeDue(Builder $query): Builder
    {
        return $query->where('next_run_at', '<=', now());
    }

    public function scopeByFrequency(Builder $query, string $frequency): Builder
    {
        return $query->where('frequency', $frequency);
    }

    // Methods

    public function toggle(): void
    {
        $this->update(['enabled' => !$this->enabled]);
    }
}
