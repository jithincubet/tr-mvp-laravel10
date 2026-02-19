<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * UserRole Model
 * 
 * Pivot model for user-role assignments.
 * Uses app_role enum: admin, instructor, trainee, viewer
 * 
 * @property int $id
 * @property int $user_id
 * @property int $client_id
 * @property string $role
 * @property \Carbon\Carbon $created_at
 */
class UserRole extends Model
{
    protected $table = 'tr2_user_roles';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'client_id',
        'role',
    ];

    protected $casts = [
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes

    public function scopeForClient(Builder $query, int $clientId): Builder
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeByRole(Builder $query, string $role): Builder
    {
        return $query->where('role', $role);
    }
}
