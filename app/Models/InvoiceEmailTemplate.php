<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Invoice Email Template Model
 * 
 * Represents an email template for invoice distribution.
 * 
 * @property int $id
 * @property string $template_type
 * @property string $subject
 * @property string $body
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class InvoiceEmailTemplate extends Model
{
    protected $table = 'inv_email_templates';

    protected $fillable = [
        'template_type',
        'subject',
        'body',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}