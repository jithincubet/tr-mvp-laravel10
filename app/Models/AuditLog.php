<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * AuditLog Model
 * 
 * Records audit trail of entity changes.
 * 
 * @property int $id
 * @property int $client_id
 * @property int|null $performed_by
 * @property string $entity_type
 * @property int $entity_id
 * @property string $action
 * @property array|null $details
 * @property \Carbon\Carbon $created_at
 */
class AuditLog extends Model
{
    protected $table = 'tr2_audit_log';

    public $timestamps = false;

    protected $fillable = [
        'client_id',
        'performed_by',
        'entity_type',
        'entity_id',
        'action',
        'details',
    ];

    protected $casts = [
        'details' => 'array',
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

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    // Scopes

    public function scopeForClient(Builder $query, int $clientId): Builder
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeByEntity(Builder $query, string $type, int $id): Builder
    {
        return $query->where('entity_type', $type)->where('entity_id', $id);
    }

    public function scopeByAction(Builder $query, string $action): Builder
    {
        return $query->where('action', $action);
    }

    public function scopeByPerformer(Builder $query, int $userId): Builder
    {
        return $query->where('performed_by', $userId);
    }

    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Static Methods

    public static function log(
        string $entityType,
        int $entityId,
        string $action,
        ?array $details = null,
        ?int $performedBy = null
    ): self {
        return self::create([
            'client_id' => auth()->user()->client_id ?? 1,
            'performed_by' => $performedBy ?? auth()->id(),
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'details' => $details,
        ]);
    }
}
