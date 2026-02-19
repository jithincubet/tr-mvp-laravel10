<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * ErrorLog Model
 * 
 * Records application errors for debugging.
 * 
 * @property int $id
 * @property int|null $client_id
 * @property int|null $user_id
 * @property string $error_type
 * @property string $message
 * @property string|null $stack_trace
 * @property string|null $source
 * @property string|null $url
 * @property string|null $user_agent
 * @property string|null $error_level
 * @property array|null $additional_context
 * @property bool $resolved
 * @property \Carbon\Carbon $created_at
 */
class ErrorLog extends Model
{
    protected $table = 'tr2_error_logs';

    public $timestamps = false;

    protected $fillable = [
        'client_id',
        'user_id',
        'user_email',
        'error_type',
        'message',
        'stack_trace',
        'source',
        'url',
        'user_agent',
        'error_level',
        'additional_context',
        'resolved',
        'resolved_at',
        'resolved_by',
    ];

    protected $casts = [
        'additional_context' => 'array',
        'resolved' => 'boolean',
        'resolved_at' => 'datetime',
        'created_at' => 'datetime',
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

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    // Scopes

    public function scopeForClient(Builder $query, int $clientId): Builder
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeUnresolved(Builder $query): Builder
    {
        return $query->where('resolved', false);
    }

    public function scopeResolved(Builder $query): Builder
    {
        return $query->where('resolved', true);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('error_type', $type);
    }

    public function scopeByLevel(Builder $query, string $level): Builder
    {
        return $query->where('error_level', $level);
    }

    public function scopeRecent(Builder $query, int $days = 7): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Methods

    public function resolve(?int $userId = null): void
    {
        $this->update([
            'resolved' => true,
            'resolved_at' => now(),
            'resolved_by' => $userId ?? auth()->id(),
        ]);
    }
}
