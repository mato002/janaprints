<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30);
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('duration_hours', 6, 2)->default(0);
            $table->boolean('requires_certification')->default(false);
            $table->unsignedSmallInteger('certificate_validity_days')->nullable();
            $table->json('skill_tags')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['company_id', 'type']);
        });

        Schema::create('employee_training_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('training_program_id')->constrained()->cascadeOnDelete();
            $table->string('reference', 40);
            $table->string('status', 20)->default('assigned');
            $table->date('due_date')->nullable();
            $table->decimal('hours_completed', 6, 2)->default(0);
            $table->string('certificate_reference')->nullable();
            $table->date('certificate_expires_at')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('assigned_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('completed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'reference'], 'emp_training_ref_unique');
            $table->index(['company_id', 'employee_id'], 'emp_train_assign_emp_idx');
            $table->index(['company_id', 'certificate_expires_at'], 'emp_train_cert_exp_idx');
        });

        Schema::create('employee_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('skill_name');
            $table->string('proficiency', 20)->default('beginner');
            $table->foreignId('source_training_assignment_id')->nullable()->constrained('employee_training_assignments')->nullOnDelete();
            $table->date('acquired_at')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'skill_name'], 'emp_skill_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_skills');
        Schema::dropIfExists('employee_training_assignments');
        Schema::dropIfExists('training_programs');
    }
};
