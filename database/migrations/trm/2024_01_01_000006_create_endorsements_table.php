<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create Endorsements, Currencies, and Related Tables
 */
return new class extends Migration
{
    public function up(): void
    {
        // Endorsement Types
        Schema::create('tr2_endorsement_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('tr2_clients')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('reference', 100)->nullable();
            $table->string('scheme', 50)->default('standard');
            $table->boolean('learner_upload')->default(false);
            $table->boolean('is_default')->default(false);
            $table->boolean('disabled')->default(false);
            $table->timestamps();
            $table->timestamp('synced_at')->nullable();
            
            $table->index(['client_id', 'disabled']);
        });

        // Endorsement Schedules
        Schema::create('tr2_endorsement_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('tr2_clients')->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->string('reference', 100)->nullable();
            $table->integer('expire_months')->default(12);
            $table->integer('check_days')->default(30);
            $table->integer('open_days')->default(30);
            $table->boolean('once')->default(false);
            $table->boolean('resume')->default(false);
            $table->string('cycles')->nullable();
            $table->json('properties')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('disabled')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->timestamp('synced_at')->nullable();
            
            $table->index(['client_id', 'disabled']);
        });

        // Endorsement Forms
        Schema::create('tr2_endorsement_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('tr2_clients')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('reference', 100)->nullable();
            $table->integer('sort_order')->default(0);
            $table->json('properties')->nullable();
            $table->boolean('disabled')->default(false);
            $table->timestamps();
            $table->timestamp('synced_at')->nullable();
        });

        // Endorsement Form Fields
        Schema::create('tr2_endorsement_form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('tr2_endorsement_forms')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('field_type', 50)->default('text');
            $table->string('placeholder')->nullable();
            $table->boolean('required')->default(false);
            $table->integer('sort_order')->default(0);
            $table->json('properties')->nullable();
            $table->boolean('disabled')->default(false);
            $table->timestamps();
            $table->timestamp('synced_at')->nullable();
            
            $table->index(['form_id', 'sort_order']);
        });

        // Endorsements
        Schema::create('tr2_endorsements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('tr2_clients')->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 50);
            $table->text('description')->nullable();
            $table->string('reference', 100)->nullable();
            $table->foreignId('type_id')->nullable()->constrained('tr2_endorsement_types')->nullOnDelete();
            $table->foreignId('schedule_id')->nullable()->constrained('tr2_endorsement_schedules')->nullOnDelete();
            $table->foreignId('form_id')->nullable()->constrained('tr2_endorsement_forms')->nullOnDelete();
            $table->unsignedBigInteger('training_id')->nullable();
            $table->unsignedBigInteger('notification_id')->nullable();
            $table->boolean('enrollment')->default(false);
            $table->boolean('disabled')->default(false);
            $table->unsignedBigInteger('legacy_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->timestamp('synced_at')->nullable();
            
            $table->unique(['client_id', 'code']);
            $table->index(['client_id', 'disabled']);
            $table->index('legacy_id');
        });

        // Event Endorsements (pivot)
        Schema::create('tr2_event_endorsements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('tr2_events')->cascadeOnDelete();
            $table->foreignId('endorsement_id')->constrained('tr2_endorsements')->cascadeOnDelete();
            $table->timestamps();
            $table->timestamp('synced_at')->nullable();
            
            $table->unique(['event_id', 'endorsement_id']);
        });

        // Form Endorsements (pivot)
        Schema::create('tr2_form_endorsements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('tr2_forms')->cascadeOnDelete();
            $table->foreignId('endorsement_id')->constrained('tr2_endorsements')->cascadeOnDelete();
            $table->timestamps();
            $table->timestamp('synced_at')->nullable();
            
            $table->unique(['form_id', 'endorsement_id']);
        });

        // Form Instructor Endorsements (required for instructors)
        Schema::create('tr2_form_instructor_endorsements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('tr2_forms')->cascadeOnDelete();
            $table->foreignId('endorsement_id')->constrained('tr2_endorsements')->cascadeOnDelete();
            $table->timestamps();
            $table->timestamp('synced_at')->nullable();
            
            $table->unique(['form_id', 'endorsement_id'], 'form_instructor_endorsement_unique');
        });

        // EMS Currencies (User qualification records)
        Schema::create('tr2_ems_currencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('tr2_clients')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('clients_users')->cascadeOnDelete();
            $table->foreignId('endorsement_id')->constrained('tr2_endorsements')->cascadeOnDelete();
            $table->foreignId('session_id')->nullable()->constrained('tr2_event_sessions')->nullOnDelete();
            $table->date('date_qualified')->nullable();
            $table->date('date_expired')->nullable();
            $table->string('status', 50)->default('active');
            $table->boolean('current')->default(true);
            $table->boolean('passes')->default(false);
            $table->boolean('fails')->default(false);
            $table->integer('score')->default(0);
            $table->integer('progress')->default(0);
            $table->text('description')->nullable();
            $table->string('reference', 100)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('disabled')->default(false);
            $table->string('ltc_base', 100)->nullable();
            $table->string('ltc_authorization_level', 50)->nullable();
            $table->string('started')->nullable();
            $table->string('ended')->nullable();
            $table->string('checked')->nullable();
            $table->string('changed')->nullable();
            $table->string('expects')->nullable();
            $table->string('schedules')->nullable();
            $table->integer('expiring_reminders_sent')->default(0);
            $table->timestamp('last_reminder_sent_at')->nullable();
            $table->unsignedBigInteger('legacy_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->timestamp('synced_at')->nullable();
            
            $table->index(['user_id', 'endorsement_id', 'current']);
            $table->index(['client_id', 'date_expired']);
            $table->index(['user_id', 'current']);
            $table->index('legacy_id');
        });

        // EMS Currency Documents (uploaded docs linked to currency records)
        Schema::create('tr2_ems_currencies_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->default(1)->constrained('tr2_clients')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('clients_users')->cascadeOnDelete();
            $table->foreignId('currency_id')->constrained('tr2_ems_currencies')->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->string('filename')->nullable();
            $table->string('file_path')->nullable();
            $table->string('content_type')->nullable();
            $table->integer('file_size')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('current')->default(true);
            $table->boolean('passes')->default(false);
            $table->boolean('fails')->default(false);
            $table->boolean('pending')->default(false);
            $table->boolean('disabled')->default(false);
            $table->boolean('admin_request')->default(false);
            $table->timestamp('admin_request_at')->nullable();
            $table->text('admin_request_info')->nullable();
            $table->integer('checker_id')->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->timestamp('synced_at')->nullable();
        });

        // EMS Endorsements Groups (batch endorsement grouping)
        Schema::create('tr2_ems_endorsements_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('endorsement_id')->constrained('tr2_endorsements')->cascadeOnDelete();
            $table->integer('group_id');
            $table->integer('relation_id')->nullable();
            $table->integer('disabled')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->timestamp('synced_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tr2_ems_endorsements_groups');
        Schema::dropIfExists('tr2_ems_currencies_documents');
        Schema::dropIfExists('tr2_ems_currencies');
        Schema::dropIfExists('tr2_form_instructor_endorsements');
        Schema::dropIfExists('tr2_form_endorsements');
        Schema::dropIfExists('tr2_event_endorsements');
        Schema::dropIfExists('tr2_endorsements');
        Schema::dropIfExists('tr2_endorsement_form_fields');
        Schema::dropIfExists('tr2_endorsement_forms');
        Schema::dropIfExists('tr2_endorsement_schedules');
        Schema::dropIfExists('tr2_endorsement_types');
    }
};
