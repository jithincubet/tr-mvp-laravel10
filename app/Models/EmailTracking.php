<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Email Tracking Model
 * 
 * Tracks email delivery status for sent invoices.
 * 
 * @property int $id
 * @property int|null $invoice_id
 * @property string|null $resend_email_id
 * @property string $recipient_email
 * @property string $sent_at
 * @property string|null $delivered_at
 * @property string|null $opened_at
 * @property string|null $clicked_at
 * @property string|null $bounced_at
 * @property int $open_count
 * @property int $click_count
 * @property string $status
 */
class EmailTracking extends Model
{
    protected $table = 'sub_email_tracking';

    public $timestamps = false;

    protected $fillable = [
        'invoice_id',
        'resend_email_id',
        'recipient_email',
        'sent_at',
        'delivered_at',
        'opened_at',
        'clicked_at',
        'bounced_at',
        'open_count',
        'click_count',
        'status',
    ];

    protected $casts = [
        'open_count' => 'integer',
        'click_count' => 'integer',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime',
        'bounced_at' => 'datetime',
    ];

    // Relationships

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(InvoiceHeader::class, 'invoice_id');
    }
}