<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Product Model
 * 
 * Represents a subscription product.
 * 
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property \Carbon\Carbon $created_at
 */
class Product extends Model
{
    protected $table = 'sub_products';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // Relationships

    public function tiers(): HasMany
    {
        return $this->hasMany(Tier::class, 'product_id');
    }

    public function clientProducts(): HasMany
    {
        return $this->hasMany(ClientProduct::class, 'product_id');
    }
}