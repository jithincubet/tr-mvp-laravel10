<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tier Bracket Model
 * 
 * Represents a pricing bracket within a tier.
 * 
 * @property int $id
 * @property int|null $tier_id
 * @property int $min_users
 * @property int|null $max_users
 * @property float $price_unit
 * @property float $price_flat_fee
 * @property \Carbon\Carbon $created_at
 */
class TierBracket extends Model
{
    protected $table = 'sub_tier_brackets';

    public $timestamps = false;

    protected $fillable = [
        'tier_id',
        'min_users',
        'max_users',
        'price_unit',
        'price_flat_fee',
    ];

    protected $casts = [
        'min_users' => 'integer',
        'max_users' => 'integer',
        'price_unit' => 'decimal:2',
        'price_flat_fee' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    // Relationships

    public function tier(): BelongsTo
    {
        return $this->belongsTo(Tier::class, 'tier_id');
    }
}