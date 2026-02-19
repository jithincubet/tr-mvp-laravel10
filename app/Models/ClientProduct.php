<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Client Product Model
 * 
 * Represents a client-product subscription assignment.
 * 
 * @property int $id
 * @property int|null $client_id
 * @property int|null $product_id
 * @property int|null $tier_id
 * @property string|null $date_from
 * @property string|null $date_to
 * @property bool $enabled
 * @property \Carbon\Carbon $created_at
 */
class ClientProduct extends Model
{
    protected $table = 'sub_client_products';

    public $timestamps = false;

    protected $fillable = [
        'client_id',
        'product_id',
        'tier_id',
        'date_from',
        'date_to',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'date_from' => 'date',
        'date_to' => 'date',
        'created_at' => 'datetime',
    ];

    // Relationships

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function tier(): BelongsTo
    {
        return $this->belongsTo(Tier::class, 'tier_id');
    }
}