<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * EmailLog Model
 * 
 * Logs sent emails for auditing and debugging.
 * 
 * @property int $id
 * @property int $client_id
 * @property int|null $template_id
 * @property string $email_type
 * @property string $recipient_email
 * @property string|null $recipient_name
 * @property string $subject
 * @property string $body_html
 * @property string $status
 * @property string|null $error_message
 * @property string|null $mailgun_message_id
 * @property string|null $resend_email_id
 * @property \Carbon\Carbon|null $sent_at
 * @property \Carbon\Carbon $created_at
 */
class EmailLog extends Model
{
    protected $table = 'tr2_email_logs';

    public $timestamps = false;

    protected $fillable = [
        'client_id',
        'template_id',
        'email_type',
        'frequency',
        'recipient_email',
        'recipient_name',
        'subject',
        'body_html',
        'status',
        'error_message',
        'mailgun_message_id',
        'resend_email_id',
        'notification_ids',
        'sent_at',
    ];

    protected $casts = [
        'notification_ids' => 'array',
        'sent_at' => 'datetime',
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

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'template_id');
    }

    // Scopes

    public function scopeForClient(Builder $query, int $clientId): Builder
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeSent(Builder $query): Builder
    {
        return $query->where('status', 'sent');
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'failed');
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('email_type', $type);
    }

    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
