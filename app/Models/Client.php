<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Client Model
 * 
 * Represents a tenant/organization in the multi-tenant system.
 * All other entities are scoped to a client.
 * 
 * @property int $id
 * @property string $name
 * @property string|null $name_legal
 * @property string|null $subdomain
 * @property string|null $logo_main
 * @property string|null $logo_icon
 * @property bool $master
 * @property string $contract
 * @property string $price_plan
 * @property string|null $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class Client extends Model
{
    use SoftDeletes;

    protected $table = 'tr2_clients';

    protected $fillable = [
        'name',
        'name_legal',
        'subdomain',
        'address',
        'address_1',
        'address_2',
        'address_3',
        'city',
        'postal_code',
        'country',
        'iata',
        'icao',
        'logo_main',
        'logo_icon',
        'master',
        'contract',
        'price_plan',
        'status',
        'date_format',
        'currency',
        'vat',
        'vat_rate',
        'vat_number',
        'organization_number',
        'billing_type',
        'billing_format',
        'billing_terms',
        'billing_address',
        'billing_city',
        'billing_postal_code',
        'billing_country',
        'billing_details',
        'email_invoice',
        'email_invoice_cc',
        'reference_name',
        'reference_email',
        'your_reference',
        'excluded_from_invoicing',
        'notes',
        'hosts',
        'parent_id',
        'divisions_enabled',
        'branches_enabled',
        'two_factor_auth_enabled',
        'facial_verification_enabled',
        'access_to_enrollments',
        'access_to_logs',
        'author_area_enabled',
        'company_logo_enabled',
        'continuous_training',
        'progression_enabled',
        'unlocking',
        'gdpr_expire_months',
        'days_overdue_until',
    ];

    protected $casts = [
        'master' => 'boolean',
        'divisions_enabled' => 'boolean',
        'branches_enabled' => 'boolean',
        'two_factor_auth_enabled' => 'boolean',
        'facial_verification_enabled' => 'boolean',
        'access_to_enrollments' => 'boolean',
        'access_to_logs' => 'boolean',
        'author_area_enabled' => 'boolean',
        'company_logo_enabled' => 'boolean',
        'continuous_training' => 'boolean',
        'progression_enabled' => 'boolean',
        'unlocking' => 'boolean',
        'excluded_from_invoicing' => 'boolean',
        'legacy' => 'boolean',
        'migrated' => 'boolean',
        'shop' => 'boolean',
        'vat' => 'decimal:2',
        'vat_rate' => 'decimal:2',
        'gdpr_expire_months' => 'integer',
        'days_overdue_until' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships

    public function parent()
    {
        return $this->belongsTo(Client::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Client::class, 'parent_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function forms(): HasMany
    {
        return $this->hasMany(Form::class);
    }

    public function endorsements(): HasMany
    {
        return $this->hasMany(Endorsement::class);
    }

    public function currencies(): HasMany
    {
        return $this->hasMany(Currency::class);
    }

    public function divisions(): HasMany
    {
        return $this->hasMany(Division::class);
    }

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    public function features(): HasMany
    {
        return $this->hasMany(Feature::class);
    }

    public function trainingFacilities(): HasMany
    {
        return $this->hasMany(TrainingFacility::class);
    }

    public function emailTemplates(): HasMany
    {
        return $this->hasMany(EmailTemplate::class);
    }

    public function notificationRules(): HasMany
    {
        return $this->hasMany(NotificationRule::class);
    }

    // Billing Relationships

    public function clientUsers(): HasMany
    {
        return $this->hasMany(ClientUser::class, 'client_id');
    }

    public function clientProducts(): HasMany
    {
        return $this->hasMany(ClientProduct::class, 'client_id');
    }

    public function billingRecords(): HasMany
    {
        return $this->hasMany(BillingRecord::class, 'client_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(InvoiceHeader::class, 'client_id');
    }
}
