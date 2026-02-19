<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create Training (LMS), Line Training, and EMS Response Tables
 */
return new class extends Migration
{
    public function up(): void
    {
        // Aircraft Types
        Schema::create('tr2_aircraft_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('tr2_clients')->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 20);
            $table->string('category', 50)->nullable();
            $table->timestamps();
            $table->timestamp('synced_at')->nullable();
            
            $table->unique(['client_id', 'code']);
        });

        // Training (LMS Courses)
        Schema::create('tr2_training', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('tr2_clients')->cascadeOnDelete();
            $table->foreignId('division_id')->nullable()->constrained('tr2_divisions')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('reference', 100)->nullable();
            $table->string('category', 100)->nullable();
            $table->string('type', 50)->default('online');
            $table->decimal('duration_hours', 5, 2)->nullable();
            $table->integer('pass_score')->nullable();
            $table->integer('max_attempts')->nullable();
            $table->boolean('disabled')->default(false);
            $table->unsignedBigInteger('legacy_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->timestamp('synced_at')->nullable();
            
            $table->index(['client_id', 'disabled']);
            $table->index('legacy_id');
        });

        // Training Enrollments
        Schema::create('tr2_training_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('tr2_clients')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('clients_users')->cascadeOnDelete();
            $table->morphs('enrollable');
            $table->string('status', 50)->default('enrolled');
            $table->integer('progress')->default(0);
            $table->integer('score')->nullable();
            $table->integer('attempts')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->timestamp('synced_at')->nullable();
            
            $table->index(['user_id', 'status']);
            $table->index(['client_id', 'status']);
        });

        // Training Sessions (LMS module completions)
        Schema::create('tr2_training_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_id')->constrained('tr2_training')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('clients_users')->cascadeOnDelete();
            $table->foreignId('endorsement_id')->nullable()->constrained('tr2_endorsements')->nullOnDelete();
            $table->string('session_type', 50)->default('attempt');
            $table->integer('score')->nullable();
            $table->boolean('passed')->default(false);
            $table->integer('duration_seconds')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('responses')->nullable();
            $table->timestamps();
            $table->timestamp('synced_at')->nullable();
            
            $table->index(['training_id', 'user_id']);
            $table->index(['user_id', 'completed_at']);
        });

        // Trainee Profiles (Line Training progress)
        Schema::create('tr2_trainee_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('clients_users')->cascadeOnDelete();
            $table->foreignId('aircraft_type_id')->nullable()->constrained('tr2_aircraft_types')->nullOnDelete();
            $table->string('training_status', 50)->default('active');
            $table->date('start_date')->nullable();
            $table->date('target_release_date')->nullable();
            $table->date('actual_release_date')->nullable();
            $table->string('current_phase', 50)->nullable();
            $table->integer('sectors_completed')->default(0);
            $table->integer('sectors_required')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->timestamp('synced_at')->nullable();
            
            $table->index('training_status');
        });

        // Exposure Types
        Schema::create('tr2_exposure_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('tr2_clients')->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 50);
            $table->string('category', 50)->nullable();
            $table->text('description')->nullable();
            $table->boolean('disabled')->default(false);
            $table->timestamps();
            $table->timestamp('synced_at')->nullable();
            
            $table->unique(['client_id', 'code']);
        });

        // Form Exposure Requirements
        Schema::create('tr2_form_exposure_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('tr2_forms')->cascadeOnDelete();
            $table->foreignId('exposure_type_id')->constrained('tr2_exposure_types')->cascadeOnDelete();
            $table->integer('min_count')->default(1);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->timestamp('synced_at')->nullable();
            
            $table->unique(['form_id', 'exposure_type_id']);
        });

        // Session Sectors
        Schema::create('tr2_session_sectors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('tr2_event_sessions')->cascadeOnDelete();
            $table->integer('sector_number')->default(1);
            $table->string('departure_airport', 10)->nullable();
            $table->string('arrival_airport', 10)->nullable();
            $table->string('flight_number', 20)->nullable();
            $table->string('aircraft_type', 20)->nullable();
            $table->string('aircraft_registration', 20)->nullable();
            $table->date('sector_date')->nullable();
            $table->string('duty_type', 50)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->timestamp('synced_at')->nullable();
            
            $table->index('session_id');
        });

        // Session Exposures
        Schema::create('tr2_session_exposures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('tr2_event_sessions')->cascadeOnDelete();
            $table->foreignId('exposure_type_id')->constrained('tr2_exposure_types')->cascadeOnDelete();
            $table->integer('count')->default(1);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->timestamp('synced_at')->nullable();
            
            $table->unique(['session_id', 'exposure_type_id']);
        });

        // Element Weakness Trends
        Schema::create('tr2_element_weakness_trends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('tr2_clients')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('clients_users')->cascadeOnDelete();
            $table->foreignId('form_id')->constrained('tr2_forms')->cascadeOnDelete();
            $table->foreignId('block_element_id')->constrained('tr2_block_elements')->cascadeOnDelete();
            $table->integer('occurrence_count')->default(1);
            $table->string('trend_status', 50)->default('monitoring');
            $table->foreignId('last_occurrence_session_id')->nullable()->constrained('tr2_event_sessions')->nullOnDelete();
            $table->timestamps();
            $table->timestamp('synced_at')->nullable();
            
            $table->unique(['user_id', 'form_id', 'block_element_id'], 'weakness_trend_unique');
        });

        // EMS Currencies Responses (learner responses against currency records)
        Schema::create('tr2_ems_currencies_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('currency_id')->constrained('tr2_ems_currencies')->cascadeOnDelete();
            $table->integer('field_id');
            $table->integer('option_id')->nullable();
            $table->text('content')->nullable();
            $table->boolean('passes')->default(false);
            $table->boolean('fails')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->timestamp('synced_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tr2_ems_currencies_responses');
        Schema::dropIfExists('tr2_element_weakness_trends');
        Schema::dropIfExists('tr2_session_exposures');
        Schema::dropIfExists('tr2_session_sectors');
        Schema::dropIfExists('tr2_form_exposure_requirements');
        Schema::dropIfExists('tr2_exposure_types');
        Schema::dropIfExists('tr2_trainee_profiles');
        Schema::dropIfExists('tr2_training_sessions');
        Schema::dropIfExists('tr2_training_enrollments');
        Schema::dropIfExists('tr2_training');
        Schema::dropIfExists('tr2_aircraft_types');
    }
};
