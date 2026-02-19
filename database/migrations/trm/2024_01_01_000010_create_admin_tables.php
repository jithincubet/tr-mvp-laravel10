<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create Administration Tables (Audit, Config, Errors, Certificates, UI Config, Sessions)
 */
return new class extends Migration
{
    public function up(): void
    {
        // Audit Log
        Schema::create('tr2_audit_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('tr2_clients')->cascadeOnDelete();
            $table->foreignId('performed_by')->nullable()->constrained('clients_users')->nullOnDelete();
            $table->string('entity_type', 50);
            $table->unsignedBigInteger('entity_id');
            $table->string('action', 50);
            $table->jsonb('details')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('synced_at')->nullable();
            
            $table->index(['client_id', 'entity_type', 'entity_id']);
            $table->index(['client_id', 'created_at']);
            $table->index('performed_by');
        });

        // System Config
        Schema::create('tr2_system_config', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Error Logs
        Schema::create('tr2_error_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained('tr2_clients')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('clients_users')->nullOnDelete();
            $table->string('user_email')->nullable();
            $table->string('error_type', 100);
            $table->string('error_level', 20)->default('error');
            $table->text('message');
            $table->text('stack_trace')->nullable();
            $table->string('source', 100)->nullable();
            $table->string('url', 500)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->jsonb('additional_context')->nullable();
            $table->boolean('resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('clients_users')->nullOnDelete();
            $table->timestamp('created_at')->nullable();
            
            $table->index(['client_id', 'error_type']);
            $table->index(['client_id', 'resolved']);
            $table->index('created_at');
        });

        // Certificate Templates
        Schema::create('tr2_certificate_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('tr2_clients')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('page_size', 20)->default('A4');
            $table->string('page_orientation', 20)->default('landscape');
            $table->string('background_image')->nullable();
            $table->jsonb('canvas_data')->nullable();
            $table->boolean('enabled')->default(true);
            $table->timestamps();
            $table->timestamp('synced_at')->nullable();
            
            $table->index(['client_id', 'enabled']);
        });

        // Certificate Assets
        Schema::create('tr2_certificate_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('tr2_clients')->cascadeOnDelete();
            $table->string('name');
            $table->string('file_type', 20)->default('image');
            $table->string('file_path');
            $table->integer('file_size')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('synced_at')->nullable();
            
            $table->index('client_id');
        });

        // Badge Color Configs
        Schema::create('tr2_badge_color_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('tr2_clients')->cascadeOnDelete();
            $table->string('badge_group');
            $table->string('badge_key');
            $table->string('bg_color');
            $table->string('fg_color');
            $table->timestamps();
            
            $table->unique(['client_id', 'badge_group', 'badge_key']);
        });

        // Dashboard Layouts
        Schema::create('tr2_dashboard_layouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('tr2_clients')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('clients_users')->cascadeOnDelete();
            $table->string('layout_name')->default('Default');
            $table->jsonb('layout_config')->default('[]');
            $table->boolean('is_default')->default(false)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Page Help Configs
        Schema::create('tr2_page_help_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('tr2_clients')->cascadeOnDelete();
            $table->string('page_key')->comment('Route or page identifier');
            $table->string('title');
            $table->text('description')->nullable();
            $table->jsonb('sections')->nullable();
            $table->timestamps();
            
            $table->unique(['client_id', 'page_key']);
        });

        // Tooltip Configs
        Schema::create('tr2_tooltip_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('tr2_clients')->cascadeOnDelete();
            $table->string('tooltip_key')->comment('UI element identifier');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('data_source')->nullable();
            $table->jsonb('filters')->nullable();
            $table->timestamps();
            
            $table->unique(['client_id', 'tooltip_key']);
        });

        // User Taskbar
        Schema::create('tr2_user_taskbar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('tr2_clients')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('clients_users')->cascadeOnDelete();
            $table->text('feature_code')->nullable();
            $table->text('custom_name')->nullable();
            $table->text('custom_route')->nullable();
            $table->text('custom_icon');
            $table->integer('sort_order');
            $table->timestamp('created_at')->nullable();
        });

        // Sessions (general session tracking)
        Schema::create('tr2_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained('clients_users')->cascadeOnDelete();
            $table->string('token')->unique();
            $table->timestamp('expires_at');
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->jsonb('device_metadata')->nullable()->default('{}');
            $table->timestamp('created_at')->nullable();
            
            $table->index('token');
            $table->index(['user_id', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tr2_sessions');
        Schema::dropIfExists('tr2_user_taskbar');
        Schema::dropIfExists('tr2_tooltip_configs');
        Schema::dropIfExists('tr2_page_help_configs');
        Schema::dropIfExists('tr2_dashboard_layouts');
        Schema::dropIfExists('tr2_badge_color_configs');
        Schema::dropIfExists('tr2_certificate_assets');
        Schema::dropIfExists('tr2_certificate_templates');
        Schema::dropIfExists('tr2_error_logs');
        Schema::dropIfExists('tr2_system_config');
        Schema::dropIfExists('tr2_audit_log');
    }
};
