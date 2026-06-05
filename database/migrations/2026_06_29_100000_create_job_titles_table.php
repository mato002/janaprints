<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_titles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('code', 50);
            $table->string('title', 120);
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->text('description')->nullable();
            $table->string('level', 30);
            $table->unsignedSmallInteger('sort_order')->default(100);
            $table->foreignId('reports_to_job_title_id')->nullable()->constrained('job_titles')->nullOnDelete();
            $table->string('approval_authority', 120)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'code'], 'job_titles_company_code_unique');
            $table->index(['company_id', 'is_active', 'sort_order'], 'job_titles_company_active_sort_idx');
            $table->index(['company_id', 'department_id'], 'job_titles_company_department_idx');
            $table->index(['company_id', 'reports_to_job_title_id'], 'job_titles_company_reports_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_titles');
    }
};
