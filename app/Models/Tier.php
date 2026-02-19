<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Tier Model
 * 
 * Represents a pricing tier for a product.
 * 
 * @property int $id
 * @property int|null $product_id
 * @property string $name
 * @property string|null $description
 * @property string[] $features
 * @property string $tier_type
 * @property \Carbon\Carbon $created_at
 */
class Tier extends Model
{
    protected $table = 'sub_tiers';

    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'name',
        'description',
        'features',
        'tier_type',
    ];

    protected $casts = [
        'features' => 'array',
        'created_at' => 'datetime',
    ];

    // Relationships

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function brackets(): HasMany
    {
        return $this->hasMany(TierBracket::class, 'tier_id');
    }

    public function clientProducts(): HasMany
    {
        return $this->hasMany(ClientProduct::class, 'tier_id');
    }
}