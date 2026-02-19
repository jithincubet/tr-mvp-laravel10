<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create Notifications and Email Tables
 */
return new class extends Migration
{
    public function up(): void
    {
        // Email Templates
        Schema::create('tr2_email_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained('tr2_clients')->cascadeOnDelete();
            $table->string('name');
            $table->string('notification_type', 50);
            $table->string('subject');
            $table->text('body_html');
            $table->json('table_config')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('enabled')->default(true);
            $table->timestamps();
            
            $table->index(['client_id', 'notification_type']);
        });

        // Notification Rules
        Schema::create('tr2_notification_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('tr2_clients')->cascadeOnDelete();
            $table->string('name');
            $table->string('trigger_type', 50);
            $table->integer('trigger_days')->nullable();
            $table->json('channels')->default('["in_app"]');
            $table->json('recipients')->nullable();
            $table->foreignId('template_id')->nullable()->constrained('tr2_email_templates')->nullOnDelete();
            $table->boolean('disabled')->default(false);
            $table->timestamps();
            $table->timestamp('synced_at')->nullable();
            
            $table->index(['client_id', 'trigger_type']);
            $table->index(['client_id', 'disabled']);
        });

        // Notifications
        Schema::create('tr2_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('tr2_clients')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('clients_users')->cascadeOnDelete();
            $table->foreignId('rule_id')->nullable()->constrained('tr2_notification_rules')->nullOnDelete();
            $table->string('type', 50);
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->timestamp('synced_at')->nullable();
            
            $table->index(['user_id', 'read_at']);
            $table->index(['client_id', 'type']);
        });

        // Scheduled Reports
        Schema::create('tr2_scheduled_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('tr2_clients')->cascadeOnDelete();
            $table->string('name');
            $table->string('report_type', 50);
            $table->string('frequency', 20)->default('weekly');
            $table->json('recipients');
            $table->foreignId('template_id')->nullable()->constrained('tr2_email_templates')->nullOnDelete();
            $table->json('filters')->nullable();
            $table->json('columns')->nullable();
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamp('next_send_at')->nullable();
            $table->boolean('disabled')->default(false);
            $table->timestamps();
            $table->timestamp('synced_at')->nullable();
            
            $table->index(['client_id', 'disabled']);
            $table->index('next_send_at');
        });

        // Email Logs
        Schema::create('tr2_email_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('tr2_clients')->cascadeOnDelete();
            $table->foreignId('template_id')->nullable()->constrained('tr2_email_templates')->nullOnDelete();
            $table->string('recipient_email');
            $table->string('recipient_name')->nullable();
            $table->string('subject');
            $table->text('body_html');
            $table->string('email_type', 50);
            $table->string('frequency', 20)->nullable();
            $table->json('notification_ids')->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->string('mailgun_message_id')->nullable();
            $table->string('resend_email_id')->nullable();
            $table->timestamps();
            
            $table->index(['client_id', 'status']);
            $table->index(['client_id', 'email_type']);
            $table->index('recipient_email');
        });

        // Email Bounces
        Schema::create('tr2_email_bounces', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('client_id')->nullable()->constrained('tr2_clients')->cascadeOnDelete();
            $table->foreignId('email_log_id')->nullable()->constrained('tr2_email_logs')->nullOnDelete();
            $table->string('email');
            $table->string('event_type', 50);
            $table->string('severity', 20)->nullable();
            $table->string('error_code', 50)->nullable();
            $table->text('reason')->nullable();
            $table->string('subject')->nullable();
            $table->string('message_id')->nullable();
            $table->string('recipient_domain')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamp('created_at')->nullable();
            
            $table->index('email');
            $table->index(['client_id', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tr2_email_bounces');
        Schema::dropIfExists('tr2_email_logs');
        Schema::dropIfExists('tr2_scheduled_reports');
        Schema::dropIfExists('tr2_notifications');
        Schema::dropIfExists('tr2_notification_rules');
        Schema::dropIfExists('tr2_email_templates');
    }
};
