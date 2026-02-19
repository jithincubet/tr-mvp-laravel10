<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create Forms, Blocks, and Block Elements Tables
 */
return new class extends Migration
{
    public function up(): void
    {
        // Block Types
        Schema::create('tr2_block_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('tr2_clients')->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
            $table->timestamp('synced_at')->nullable();
            
            $table->index('client_id');
        });

        // Gradings
        Schema::create('tr2_gradings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('tr2_clients')->cascadeOnDelete();
            $table->string('name');
            $table->string('type', 50)->default('numeric');
            $table->json('scale')->nullable();
            $table->string('pass_value', 20)->nullable();
            $table->boolean('disabled')->default(false);
            $table->timestamps();
            $table->timestamp('synced_at')->nullable();
            
            $table->index(['client_id', 'disabled']);
        });

        // Forms
        Schema::create('tr2_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('tr2_clients')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('reference', 100)->nullable();
            $table->string('category', 100)->nullable();
            $table->boolean('enabled')->default(true);
            $table->boolean('is_line_training')->default(false);
            $table->integer('required_sectors')->nullable();
            $table->integer('exposure_count_min')->nullable();
            $table->integer('exposure_count_airport')->nullable();
            $table->integer('exposure_count_weather')->nullable();
            $table->json('release_criteria')->nullable();
            $table->unsignedBigInteger('legacy_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->timestamp('synced_at')->nullable();
            
            $table->index(['client_id', 'enabled']);
            $table->index('legacy_id');
        });

        // Blocks
        Schema::create('tr2_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('tr2_clients')->cascadeOnDelete();
            $table->foreignId('form_id')->nullable()->constrained('tr2_forms')->cascadeOnDelete();
            $table->foreignId('tbt_id')->nullable()->constrained('tr2_block_types')->nullOnDelete();
            $table->string('name');
            $table->integer('sortorder')->default(0);
            $table->boolean('enabled')->default(true);
            $table->text('guidance_text')->nullable();
            $table->text('syllabus_text')->nullable();
            $table->timestamps();
            $table->timestamp('synced_at')->nullable();
            
            $table->index(['form_id', 'sortorder']);
            $table->index(['client_id', 'enabled']);
        });

        // Block Elements
        Schema::create('tr2_block_elements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->constrained('tr2_blocks')->cascadeOnDelete();
            $table->foreignId('grading_id')->nullable()->constrained('tr2_gradings')->nullOnDelete();
            $table->string('description', 500);
            $table->integer('sortorder')->default(0);
            $table->boolean('enabled')->default(true);
            $table->boolean('mandatory')->default(false);
            $table->boolean('is_critical')->default(false);
            $table->boolean('fail_triggers_additional_training')->default(false);
            $table->text('guidance_text')->nullable();
            $table->text('syllabus_text')->nullable();
            $table->timestamps();
            $table->timestamp('synced_at')->nullable();
            
            $table->index(['parent_id', 'sortorder']);
            $table->index(['parent_id', 'enabled']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tr2_block_elements');
        Schema::dropIfExists('tr2_blocks');
        Schema::dropIfExists('tr2_forms');
        Schema::dropIfExists('tr2_gradings');
        Schema::dropIfExists('tr2_block_types');
    }
};
