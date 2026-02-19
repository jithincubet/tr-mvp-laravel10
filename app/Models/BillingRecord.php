<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * Billing Record Model
 * 
 * Represents an individual billing record linking a user to a course.
 * 
 * @property int $id
 * @property int|null $client_id
 * @property int|null $user_id
 * @property int $course_id
 * @property float $price
 * @property int|null $invoice_body_id
 * @property \Carbon\Carbon|null $expired_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class BillingRecord extends Model
{
    use SoftDeletes;

    protected $table = 'inv_billing_records';

    public $timestamps = false;

    protected $fillable = [
        'client_id',
        'user_id',
        'course_id',
        'price',
        'invoice_body_id',
        'expired_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'created_at' => 'datetime',
        'expired_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(ClientUser::class, 'user_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function invoiceDetail(): BelongsTo
    {
        return $this->belongsTo(InvoiceDetail::class, 'invoice_body_id');
    }

    // Scopes

    public function scopeUnbilled(Builder $query): Builder
    {
        return $query->whereNull('invoice_body_id');
    }

    public function scopeForClient(Builder $query, int $clientId): Builder
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeForInvoice(Builder $query, int $invoiceBodyId): Builder
    {
        return $query->where('invoice_body_id', $invoiceBodyId);
    }
}