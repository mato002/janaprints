<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('category', 40);
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('current_version')->default(0);
            $table->date('expires_at')->nullable();
            $table->unsignedSmallInteger('renewal_reminder_days')->default(30);
            $table->foreignId('uploaded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['company_id', 'employee_id']);
            $table->index(['company_id', 'expires_at']);
            $table->index(['company_id', 'category']);
        });

        Schema::create('employee_document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_document_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->string('original_name');
            $table->string('path');
            $table->string('mime_type', 120)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->foreignId('uploaded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['employee_document_id', 'version_number'], 'emp_doc_ver_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_document_versions');
        Schema::dropIfExists('employee_documents');
    }
};
