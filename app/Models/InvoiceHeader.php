<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Invoice Header Model
 * 
 * Represents an invoice with totals, status, and period information.
 * 
 * @property int $id
 * @property int|null $client_id
 * @property int $invoice_number
 * @property int $user_count
 * @property string $currency
 * @property float $vat_rate
 * @property float $net_total
 * @property float $vat_amount
 * @property float|null $rounding
 * @property float $total
 * @property string $status
 * @property string $status_date
 * @property string|null $period_start
 * @property string|null $period_end
 * @property string|null $our_reference
 * @property string|null $client_reference
 * @property int|null $payment_terms_days
 * @property string|null $due_date
 * @property string|null $pdf_storage_path
 * @property \Carbon\Carbon|null $pdf_generated_at
 * @property \Carbon\Carbon $created_at
 */
class InvoiceHeader extends Model
{
    protected $table = 'inv_header';

    public $timestamps = false;

    protected $fillable = [
        'client_id',
        'invoice_number',
        'user_count',
        'currency',
        'vat_rate',
        'net_total',
        'vat_amount',
        'rounding',
        'total',
        'status',
        'status_date',
        'period_start',
        'period_end',
        'our_reference',
        'client_reference',
        'payment_terms_days',
        'due_date',
        'pdf_storage_path',
        'pdf_generated_at',
    ];

    protected $casts = [
        'invoice_number' => 'integer',
        'user_count' => 'integer',
        'vat_rate' => 'decimal:2',
        'net_total' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'rounding' => 'decimal:2',
        'total' => 'decimal:2',
        'payment_terms_days' => 'integer',
        'pdf_generated_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    // Relationships

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(InvoiceDetail::class, 'invoice_header_id');
    }

    public function emailTracking(): HasMany
    {
        return $this->hasMany(EmailTracking::class, 'invoice_id');
    }
}