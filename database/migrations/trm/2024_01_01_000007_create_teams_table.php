<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create Teams and Team Users Tables
 */
return new class extends Migration
{
    public function up(): void
    {
        // Teams (Users Collections)
        Schema::create('tr2_users_collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('tr2_clients')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type', 50)->default('team');
            $table->boolean('disabled')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->timestamp('synced_at')->nullable();
            
            $table->index(['client_id', 'type']);
            $table->index(['client_id', 'disabled']);
        });

        // Team Users (Pivot)
        Schema::create('tr2_team_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('tr2_users_collections')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('clients_users')->cascadeOnDelete();
            $table->string('role', 50)->default('member');
            $table->timestamps();
            $table->timestamp('synced_at')->nullable();
            
            $table->unique(['team_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tr2_team_users');
        Schema::dropIfExists('tr2_users_collections');
    }
};
