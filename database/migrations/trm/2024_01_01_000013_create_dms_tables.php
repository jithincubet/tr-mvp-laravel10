<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create Document Management System (DMS) Tables
 */
return new class extends Migration
{
    public function up(): void
    {
        // DMS Categories (hierarchical)
        Schema::create('tr2_dms_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->default(1)->constrained('tr2_clients')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('tr2_dms_categories')->nullOnDelete();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['client_id', 'parent_id']);
        });

        // DMS Documents
        Schema::create('tr2_dms_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->default(1)->constrained('tr2_clients')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('clients_users')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('tr2_dms_categories')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('draft'); // draft, published
            $table->string('priority')->default('normal'); // critical, high, normal, low
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['client_id', 'status']);
            $table->index(['client_id', 'priority']);
        });

        // DMS Document Files (attachments)
        Schema::create('tr2_dms_document_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('tr2_dms_documents')->cascadeOnDelete();
            $table->foreignId('client_id')->default(1)->constrained('tr2_clients')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('filename');
            $table->string('content_type')->nullable();
            $table->integer('file_size')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->softDeletes();
            
            $table->index('document_id');
        });

        // DMS Distribution (target audiences)
        Schema::create('tr2_dms_distribution', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('tr2_dms_documents')->cascadeOnDelete();
            $table->foreignId('client_id')->default(1)->constrained('tr2_clients')->cascadeOnDelete();
            $table->string('target_type'); // user, team
            $table->integer('target_id');
            $table->timestamp('created_at')->useCurrent();
            
            $table->index(['document_id', 'target_type']);
        });

        // DMS Acknowledgements
        Schema::create('tr2_dms_acknowledgements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('tr2_dms_documents')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('clients_users')->cascadeOnDelete();
            $table->foreignId('client_id')->default(1)->constrained('tr2_clients')->cascadeOnDelete();
            $table->timestamp('acknowledged_at')->useCurrent();
            $table->timestamp('created_at')->useCurrent();
            
            $table->unique(['document_id', 'user_id']);
        });

        // DMS Library Folders
        Schema::create('tr2_dms_library_folders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->default(1)->constrained('tr2_clients')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('tr2_dms_library_folders')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['client_id', 'parent_id']);
        });

        // DMS Library Files
        Schema::create('tr2_dms_library_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->default(1)->constrained('tr2_clients')->cascadeOnDelete();
            $table->foreignId('folder_id')->constrained('tr2_dms_library_folders')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('filename');
            $table->string('content_type')->nullable();
            $table->integer('file_size')->nullable();
            $table->integer('sort_order')->default(0);
            $table->foreignId('uploaded_by')->nullable()->constrained('clients_users')->nullOnDelete();
            $table->string('status')->default('draft');
            $table->date('effective_date')->nullable();
            $table->text('text_content')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->softDeletes();
            
            $table->index(['folder_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tr2_dms_library_files');
        Schema::dropIfExists('tr2_dms_library_folders');
        Schema::dropIfExists('tr2_dms_acknowledgements');
        Schema::dropIfExists('tr2_dms_distribution');
        Schema::dropIfExists('tr2_dms_document_files');
        Schema::dropIfExists('tr2_dms_documents');
        Schema::dropIfExists('tr2_dms_categories');
    }
};
