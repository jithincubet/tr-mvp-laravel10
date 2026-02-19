<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create Clients Table
 * Core multi-tenant organization table
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tr2_clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_legal')->nullable();
            $table->string('subdomain')->nullable()->unique();
            $table->string('hosts')->nullable();
            $table->string('status')->default('active');
            $table->string('plan')->nullable();
            $table->enum('price_plan', ['free', 'starter', 'professional', 'enterprise'])->default('starter');
            $table->enum('contract', ['monthly', 'yearly', 'custom'])->default('monthly');
            
            // Contact Information
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
            $table->string('reference_name')->nullable();
            $table->string('reference_email')->nullable();
            
            // Billing Information
            $table->string('billing_address')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_postal_code')->nullable();
            $table->string('billing_country')->nullable();
            $table->string('billing_ref_id')->nullable();
            $table->string('billing_type')->nullable();
            $table->string('billing_format')->nullable();
            $table->string('billing_methods')->nullable();
            $table->text('billing_details')->nullable();
            $table->text('billing_terms')->nullable();
            $table->string('email_invoice')->nullable();
            $table->string('email_invoice_cc')->nullable();
            $table->integer('invoice_number')->nullable();
            $table->timestamp('billing_info_popup_at')->nullable();
            $table->decimal('vat', 5, 2)->nullable();
            $table->string('vat_number')->nullable();
            $table->string('organization_number')->nullable();
            $table->string('currency', 3)->default('EUR');
            $table->integer('unpaid_invoice_limit')->nullable();
            $table->decimal('unpaid_balance_limit', 10, 2)->nullable();
            $table->timestamp('unpaid_grace_until')->nullable();
            
            // Aviation Identifiers
            $table->string('iata', 3)->nullable();
            $table->string('icao', 4)->nullable();
            
            // Feature Flags
            $table->boolean('master')->default(false);
            $table->boolean('legacy')->default(false);
            $table->boolean('legacy_sync')->default(false);
            $table->boolean('legacy_billing')->default(false);
            $table->boolean('migrated')->default(false);
            $table->boolean('shop')->default(false);
            $table->boolean('unlocking')->default(false);
            $table->boolean('continuous_training')->default(false);
            $table->boolean('divisions_enabled')->default(false);
            $table->boolean('branches_enabled')->default(false);
            $table->boolean('progression_enabled')->default(false);
            $table->boolean('author_area_enabled')->default(false);
            $table->boolean('company_logo_enabled')->default(false);
            $table->boolean('custom_certificate_name')->default(false);
            $table->boolean('two_factor_auth_enabled')->default(false);
            $table->boolean('facial_verification_enabled')->default(false);
            $table->boolean('user_attributes_enabled')->default(false);
            $table->boolean('is_employment_code_required')->default(false);
            $table->boolean('question_randomisation_control')->default(false);
            $table->boolean('one_time_training_expires_option')->default(false);
            $table->boolean('enable_retirement_scheduling')->default(false);
            $table->boolean('reduce_module')->default(false);
            $table->boolean('access_to_enrollments')->default(true);
            $table->boolean('access_to_logs')->default(true);
            
            // Settings
            $table->string('date_format', 20)->default('Y-m-d');
            $table->integer('days_overdue_until')->default(30);
            $table->integer('gdpr_expire_months')->default(36);
            
            // Branding
            $table->string('logo_main')->nullable();
            $table->string('logo_icon')->nullable();
            
            // External Integrations
            $table->bigInteger('hubspot_company_id')->nullable();
            $table->string('saml2_tenant_uuid')->nullable();
            $table->unsignedBigInteger('legacy_id')->nullable();
            
            // Hierarchy
            $table->foreignId('parent_id')->nullable()->constrained('tr2_clients')->nullOnDelete();
            
            // Notes
            $table->text('notes')->nullable();
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            $table->timestamp('synced_at')->nullable();
            
            // Indexes
            $table->index('subdomain');
            $table->index('status');
            $table->index('legacy_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tr2_clients');
    }
};
