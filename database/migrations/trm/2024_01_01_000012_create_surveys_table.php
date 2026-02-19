<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create Surveys Module Tables
 */
return new class extends Migration
{
    public function up(): void
    {
        // Surveys
        Schema::create('tr2_surveys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('tr2_clients')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('reference')->nullable();
            $table->integer('sort_order')->default(0);
            $table->jsonb('properties')->nullable();
            $table->boolean('disabled')->default(false);
            $table->timestamps();
            $table->timestamp('synced_at')->nullable();
            
            $table->index(['client_id', 'disabled']);
        });

        // Survey Questions
        Schema::create('tr2_survey_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained('tr2_surveys')->cascadeOnDelete();
            $table->string('question_text');
            $table->string('question_type', 50)->default('text'); // text, rating, mcq, scale
            $table->text('description')->nullable();
            $table->jsonb('options')->nullable();
            $table->jsonb('properties')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('required')->default(false);
            $table->boolean('disabled')->default(false);
            $table->timestamps();
            $table->timestamp('synced_at')->nullable();
            
            $table->index(['survey_id', 'sort_order']);
        });

        // Survey Distributions (per-user assignments)
        Schema::create('tr2_survey_distributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained('tr2_surveys')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('tr2_clients')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('clients_users')->cascadeOnDelete();
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('due_date')->nullable();
            $table->text('status')->default('pending');
            $table->timestamp('invitation_sent_at')->nullable();
            $table->timestamp('last_reminder_at')->nullable();
            $table->integer('reminders_sent')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->timestamp('synced_at')->nullable();
            
            $table->index(['survey_id', 'user_id']);
        });

        // Survey Responses
        Schema::create('tr2_survey_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('tr2_survey_questions')->cascadeOnDelete();
            $table->foreignId('distribution_id')->constrained('tr2_survey_distributions')->cascadeOnDelete();
            $table->jsonb('response_value')->nullable();
            $table->timestamp('responded_at')->useCurrent();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('synced_at')->nullable();
            
            $table->index(['question_id']);
            $table->index(['distribution_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tr2_survey_responses');
        Schema::dropIfExists('tr2_survey_distributions');
        Schema::dropIfExists('tr2_survey_questions');
        Schema::dropIfExists('tr2_surveys');
    }
};
