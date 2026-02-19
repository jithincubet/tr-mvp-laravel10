<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Invoice Detail Model
 * 
 * Represents an invoice line item.
 * 
 * @property int $id
 * @property int|null $invoice_header_id
 * @property int|null $product_id
 * @property int|null $tier_id
 * @property string|null $tier_type
 * @property string|null $description
 * @property float $price
 * @property int $quantity
 * @property \Carbon\Carbon $created_at
 */
class InvoiceDetail extends Model
{
    protected $table = 'inv_details';

    public $timestamps = false;

    protected $fillable = [
        'invoice_header_id',
        'product_id',
        'tier_id',
        'tier_type',
        'description',
        'price',
        'quantity',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'created_at' => 'datetime',
    ];

    // Relationships

    public function header(): BelongsTo
    {
        return $this->belongsTo(InvoiceHeader::class, 'invoice_header_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function tier(): BelongsTo
    {
        return $this->belongsTo(Tier::class, 'tier_id');
    }

    public function billingRecords(): HasMany
    {
        return $this->hasMany(BillingRecord::class, 'invoice_body_id');
    }
}