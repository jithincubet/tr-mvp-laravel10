<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Invoice Setting Model
 * 
 * Singleton settings for invoice generation and company details.
 * 
 * @property int $id
 * @property string $company_name
 * @property string $company_reference
 * @property int $next_invoice_number
 * @property string $sales_manager_email
 * @property int $renewal_reminder_days
 * @property string $date_format
 * @property float $vat_percentage
 * @property string|null $company_address
 * @property string|null $company_city
 * @property string|null $company_postal_code
 * @property string|null $company_country
 * @property string|null $bank_giro
 * @property string|null $iban
 * @property int|null $payment_terms_days
 * @property string|null $interest_rate
 * @property string|null $legal_disclaimer
 * @property string|null $bic
 * @property string|null $vat_reg_no
 * @property string|null $org_no
 * @property string|null $bank
 * @property string|null $local_of_board
 * @property string|null $logo_url
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class InvoiceSetting extends Model
{
    protected $table = 'inv_settings';

    protected $fillable = [
        'company_name',
        'company_reference',
        'next_invoice_number',
        'sales_manager_email',
        'renewal_reminder_days',
        'date_format',
        'vat_percentage',
        'company_address',
        'company_city',
        'company_postal_code',
        'company_country',
        'bank_giro',
        'iban',
        'payment_terms_days',
        'interest_rate',
        'legal_disclaimer',
        'bic',
        'vat_reg_no',
        'org_no',
        'bank',
        'local_of_board',
        'logo_url',
    ];

    protected $casts = [
        'next_invoice_number' => 'integer',
        'renewal_reminder_days' => 'integer',
        'vat_percentage' => 'decimal:2',
        'payment_terms_days' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the singleton settings instance.
     */
    public static function instance(): self
    {
        return static::firstOrFail();
    }
}