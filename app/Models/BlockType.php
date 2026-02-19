<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

/**
 * BlockType Model
 * 
 * Defines categories/types for blocks.
 * 
 * @property int $id
 * @property int $client_id
 * @property string $name
 * @property \Carbon\Carbon $created_at
 */
class BlockType extends Model
{
    protected $table = 'tr2_block_types';

    public $timestamps = false;

    protected $fillable = [
        'client_id',
        'name',
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

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(Block::class, 'tbt_id');
    }

    // Scopes

    public function scopeForClient(Builder $query, int $clientId): Builder
    {
        return $query->where('client_id', $clientId);
    }
}
