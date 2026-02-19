<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create Users (clients_users), Divisions, and User Credentials Tables
 * 
 * NOTE: The primary users table is named `clients_users` (not `tr2_users`).
 * All foreign key references across the system point to `clients_users`.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Divisions table (users reference this)
        Schema::create('tr2_divisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('tr2_clients')->cascadeOnDelete();
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('post_code')->nullable();
            $table->string('country')->nullable();
            $table->string('vat_number')->nullable();
            $table->string('reference_name')->nullable();
            $table->string('reference_email')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_locked')->default(false);
            $table->uuid('uuid')->nullable()->unique();
            $table->timestamps();
            $table->softDeletes();
            $table->timestamp('synced_at')->nullable();
            
            $table->index(['client_id', 'is_default']);
        });

        // Users table (named clients_users, NOT tr2_users)
        Schema::create('clients_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->default(1)->constrained('tr2_clients')->cascadeOnDelete();
            $table->string('email');
            $table->string('first_name');
            $table->string('last_name');
            $table->foreignId('division_id')->nullable()->constrained('tr2_divisions')->nullOnDelete();
            $table->string('phone', 50)->default('');
            $table->string('country', 100)->default('')->nullable();
            $table->integer('country_id')->nullable();
            $table->date('birthdate')->nullable();
            $table->string('gender', 50)->default('')->nullable();
            $table->string('timezone', 100)->default('')->nullable();
            $table->string('avatar', 255)->default('')->nullable();
            $table->smallInteger('avatar_style')->default(0)->nullable();
            $table->string('occupation', 255)->default('')->nullable();
            $table->string('vat_number', 50)->nullable();
            $table->text('properties')->nullable();

            // Legacy & migration fields
            $table->integer('legacy_id')->default(0);
            $table->integer('legacy_client_id')->default(0);
            $table->integer('legacy')->default(0);

            // Social auth
            $table->string('auth_facebook', 255)->default('');
            $table->string('auth_google', 255)->default('');
            $table->string('auth_microsoft', 255)->nullable();

            // Session & security
            $table->string('remember_app', 100)->nullable();
            $table->string('remember_token', 100)->nullable();
            $table->string('secret', 200)->nullable();
            $table->string('stripe_user_id', 50)->nullable();
            $table->boolean('password_change_required')->default(false)->nullable();

            // 2FA
            $table->boolean('google2fa')->default(false);
            $table->string('google2fa_secret', 255)->nullable();
            $table->string('google2fa_otp', 45)->nullable();
            $table->timestamp('otp_expires_at')->nullable();

            // GDPR
            $table->boolean('gdpr_expire_status')->default(false);
            $table->timestamp('gdpr_expire_date')->nullable();

            // Marketing
            $table->boolean('marketing_agreement')->default(false);
            $table->smallInteger('marketing_source')->nullable();

            // UI preferences
            $table->boolean('show_update_notification')->default(true);

            // Timestamps
            $table->timestamps();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->softDeletes();
            $table->timestamp('synced_at')->nullable();
            
            $table->index(['client_id', 'email']);
        });

        // User credentials table (separate for security)
        Schema::create('tr2_user_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('clients_users')->cascadeOnDelete();
            $table->string('password_hash');
            $table->string('pin_code')->nullable();
            $table->string('reset_token', 100)->nullable();
            $table->timestamp('reset_token_expires_at')->nullable();
            $table->string('two_factor_secret')->nullable();
            $table->boolean('two_factor_enabled')->default(false);
            $table->timestamps();
            $table->timestamp('synced_at')->nullable();
            
            $table->index('reset_token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tr2_user_credentials');
        Schema::dropIfExists('clients_users');
        Schema::dropIfExists('tr2_divisions');
    }
};
