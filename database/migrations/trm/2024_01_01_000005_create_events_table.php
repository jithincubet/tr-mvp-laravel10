<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create Events and Event Sessions Tables
 */
return new class extends Migration
{
    public function up(): void
    {
        // Training Facilities
        Schema::create('tr2_facility_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('tr2_clients')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('disabled')->default(false);
            $table->timestamps();
            $table->timestamp('synced_at')->nullable();
        });

        Schema::create('tr2_training_facilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('tr2_clients')->cascadeOnDelete();
            $table->foreignId('type_id')->nullable()->constrained('tr2_facility_types')->nullOnDelete();
            $table->string('name');
            $table->string('code', 50)->nullable();
            $table->text('description')->nullable();
            $table->string('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('timezone', 50)->default('UTC');
            $table->boolean('disabled')->default(false);
            $table->timestamps();
            $table->timestamp('synced_at')->nullable();
            
            $table->index(['client_id', 'disabled']);
        });

        // Events
        Schema::create('tr2_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('tr2_clients')->cascadeOnDelete();
            $table->foreignId('form_id')->nullable()->constrained('tr2_forms')->nullOnDelete();
            $table->foreignId('facility_id')->nullable()->constrained('tr2_training_facilities')->nullOnDelete();
            $table->foreignId('instructor_id')->nullable()->constrained('clients_users')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('location')->nullable();
            $table->string('status', 50)->default('scheduled');
            $table->integer('max_participants')->nullable();
            $table->boolean('is_private')->default(false);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('legacy_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->timestamp('synced_at')->nullable();
            
            $table->index(['client_id', 'status']);
            $table->index(['client_id', 'start_date']);
            $table->index('instructor_id');
            $table->index('legacy_id');
        });

        // Event Participants
        Schema::create('tr2_event_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('tr2_events')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('clients_users')->cascadeOnDelete();
            $table->timestamps();
            $table->timestamp('synced_at')->nullable();
            
            $table->unique(['event_id', 'user_id']);
        });

        // Event Sessions
        Schema::create('tr2_event_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('tr2_clients')->cascadeOnDelete();
            $table->foreignId('event_id')->constrained('tr2_events')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('clients_users')->cascadeOnDelete();
            $table->foreignId('instructor_id')->nullable()->constrained('clients_users')->nullOnDelete();
            $table->date('session_date')->nullable();
            $table->integer('session_number')->nullable();
            $table->string('status', 50)->default('pending');
            $table->string('pass_fail_result', 20)->nullable();
            $table->boolean('is_approved')->default(false);
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('clients_users')->nullOnDelete();
            $table->text('notes_public')->nullable();
            $table->text('notes_private')->nullable();
            $table->text('notes_admin')->nullable();
            $table->string('flight_number', 20)->nullable();
            $table->string('flight_route', 100)->nullable();
            $table->string('aircraft_registration', 20)->nullable();
            $table->string('sector_type', 50)->nullable();
            $table->boolean('is_line_check')->default(false);
            $table->string('ltc_recommendation', 50)->nullable();
            $table->text('ltc_recommendation_notes')->nullable();
            $table->text('crm_assessment')->nullable();
            $table->boolean('requires_admin_attention')->default(false);
            $table->string('admin_attention_reason', 500)->nullable();
            $table->text('signature_data')->nullable();
            $table->json('endorsement_ids')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->string('archive_reason', 500)->nullable();
            $table->string('termination_reason', 500)->nullable();
            $table->text('termination_notes')->nullable();
            $table->unsignedBigInteger('legacy_id')->nullable();
            $table->timestamps();
            $table->timestamp('synced_at')->nullable();
            
            $table->index(['client_id', 'status']);
            $table->index(['event_id', 'user_id']);
            $table->index(['user_id', 'session_date']);
            $table->index('instructor_id');
            $table->index('legacy_id');
        });

        // Event Session Grades
        Schema::create('tr2_event_session_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('tr2_event_sessions')->cascadeOnDelete();
            $table->foreignId('block_element_id')->constrained('tr2_block_elements')->cascadeOnDelete();
            $table->string('grade_value', 20)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->timestamp('synced_at')->nullable();
            
            $table->unique(['session_id', 'block_element_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tr2_event_session_grades');
        Schema::dropIfExists('tr2_event_sessions');
        Schema::dropIfExists('tr2_event_participants');
        Schema::dropIfExists('tr2_events');
        Schema::dropIfExists('tr2_training_facilities');
        Schema::dropIfExists('tr2_facility_types');
    }
};
