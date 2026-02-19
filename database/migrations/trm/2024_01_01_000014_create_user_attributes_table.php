<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create User Custom Attributes Tables
 */
return new class extends Migration
{
    public function up(): void
    {
        // User Attribute Definitions
        Schema::create('tr2_user_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('tr2_clients')->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('clients_users')->nullOnDelete();
            $table->string('slug');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('format', 50)->default('text'); // text, number, list, multiple-select
            $table->string('status', 20)->default('enabled'); // enabled, disabled
            $table->boolean('is_default')->default(false);
            $table->boolean('mandatory_for_admin')->default(false);
            $table->boolean('mandatory_for_user')->default(false);
            $table->jsonb('properties')->nullable(); // for list/select-type options
            $table->timestamps();
            $table->timestamp('synced_at')->nullable();
            
            $table->index(['client_id', 'status']);
        });

        // User Attribute Values
        Schema::create('tr2_user_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained('tr2_user_attributes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('clients_users')->cascadeOnDelete();
            $table->text('value')->nullable();
            $table->timestamps();
            $table->timestamp('synced_at')->nullable();
            
            $table->unique(['attribute_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tr2_user_attribute_values');
        Schema::dropIfExists('tr2_user_attributes');
    }
};
