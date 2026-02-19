<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create Exercises/Quiz Module Tables
 */
return new class extends Migration
{
    public function up(): void
    {
        // Exercises (quiz/assessment definitions)
        Schema::create('tr2_exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('tr2_clients')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('published')->default(false);
            $table->integer('pass_percentage')->default(70);
            $table->integer('time_limit_mins')->nullable();
            $table->boolean('randomize_questions')->default(false);
            $table->boolean('randomize_answers')->default(false);
            $table->boolean('disabled')->default(false);
            $table->timestamps();
            $table->timestamp('synced_at')->nullable();
            
            $table->index(['client_id', 'disabled']);
        });

        // Exercise Questions
        Schema::create('tr2_exercise_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exercise_id')->constrained('tr2_exercises')->cascadeOnDelete();
            $table->text('content');
            $table->string('format', 50)->default('multiple_choice'); // multiple_choice, true_false, text
            $table->text('explanation')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('published')->default(true);
            $table->timestamps();
            $table->timestamp('synced_at')->nullable();
            
            $table->index(['exercise_id', 'sort_order']);
        });

        // Exercise Responses (answer options for questions)
        Schema::create('tr2_exercise_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('tr2_exercise_questions')->cascadeOnDelete();
            $table->text('content');
            $table->text('label')->nullable();
            $table->boolean('correct')->default(false);
            $table->boolean('published')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->timestamp('synced_at')->nullable();
            
            $table->index('question_id');
        });

        // Exercise Sessions (user attempts)
        Schema::create('tr2_exercise_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exercise_id')->constrained('tr2_exercises')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('clients_users')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('tr2_clients')->cascadeOnDelete();
            $table->foreignId('training_session_id')->nullable()->constrained('tr2_training_sessions')->nullOnDelete();
            $table->integer('score')->nullable();
            $table->integer('attempt')->default(1);
            $table->integer('progress')->default(0);
            $table->boolean('passed')->nullable();
            $table->string('status', 50)->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->timestamp('synced_at')->nullable();
            
            $table->index(['exercise_id', 'user_id']);
            $table->index(['client_id', 'status']);
        });

        // Exercise Events (answer audit log per session)
        Schema::create('tr2_exercise_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('tr2_exercise_sessions')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('tr2_exercise_questions')->cascadeOnDelete();
            $table->foreignId('response_id')->nullable()->constrained('tr2_exercise_responses')->nullOnDelete();
            $table->text('text_response')->nullable();
            $table->boolean('is_correct')->nullable();
            $table->timestamp('created_at')->nullable();
            
            $table->index(['session_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tr2_exercise_events');
        Schema::dropIfExists('tr2_exercise_sessions');
        Schema::dropIfExists('tr2_exercise_responses');
        Schema::dropIfExists('tr2_exercise_questions');
        Schema::dropIfExists('tr2_exercises');
    }
};
