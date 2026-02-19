<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create Roles, Features, and Role-Feature Pivot Tables
 */
return new class extends Migration
{
    public function up(): void
    {
        // Roles table
        Schema::create('tr2_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('tr2_clients')->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 50)->unique();
            $table->text('description')->nullable();
            $table->string('color', 7)->default('#6B7280');
            $table->boolean('is_default')->default(false);
            $table->boolean('disabled')->default(false);
            $table->integer('sortorder')->default(0);
            $table->timestamps();
            $table->timestamp('synced_at')->nullable();
            
            $table->index(['client_id', 'code']);
            $table->index(['client_id', 'disabled']);
        });

        // Features table
        Schema::create('tr2_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('tr2_clients')->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 50);
            $table->text('description')->nullable();
            $table->string('category', 50)->nullable();
            $table->string('route')->nullable();
            $table->string('section', 50)->nullable();
            $table->boolean('disabled')->default(false);
            $table->integer('sortorder')->default(0);
            $table->timestamps();
            $table->timestamp('synced_at')->nullable();
            
            $table->unique(['client_id', 'code']);
            $table->index(['client_id', 'category']);
        });

        // Role-Feature pivot table
        Schema::create('tr2_role_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('tr2_roles')->cascadeOnDelete();
            $table->foreignId('feature_id')->constrained('tr2_features')->cascadeOnDelete();
            $table->boolean('enabled')->default(true);
            $table->timestamps();
            $table->timestamp('synced_at')->nullable();
            
            $table->unique(['role_id', 'feature_id']);
        });

        // User-Role pivot table
        Schema::create('tr2_user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('clients_users')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('tr2_roles')->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->timestamp('synced_at')->nullable();
            
            $table->unique(['user_id', 'role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tr2_user_roles');
        Schema::dropIfExists('tr2_role_features');
        Schema::dropIfExists('tr2_features');
        Schema::dropIfExists('tr2_roles');
    }
};
