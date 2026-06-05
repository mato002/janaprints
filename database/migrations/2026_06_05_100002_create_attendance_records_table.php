<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained()->nullOnDelete();
            $table->date('attendance_date');
            $table->dateTime('clock_in_at')->nullable();
            $table->dateTime('clock_out_at')->nullable();
            $table->string('clock_in_device')->nullable();
            $table->string('clock_in_ip', 45)->nullable();
            $table->string('clock_in_location')->nullable();
            $table->string('clock_out_device')->nullable();
            $table->string('clock_out_ip', 45)->nullable();
            $table->string('clock_out_location')->nullable();
            $table->decimal('scheduled_hours', 5, 2)->default(0);
            $table->decimal('actual_hours', 5, 2)->nullable();
            $table->unsignedSmallInteger('late_minutes')->default(0);
            $table->decimal('overtime_hours', 5, 2)->default(0);
            $table->string('status', 20)->default('absent');
            $table->string('method', 20)->default('manual');
            $table->text('notes')->nullable();
            $table->boolean('is_manual')->default(false);
            $table->foreignId('adjusted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'attendance_date']);
            $table->index(['company_id', 'attendance_date']);
            $table->index(['branch_id', 'attendance_date']);
            $table->index(['department_id', 'attendance_date']);
            $table->index(['shift_id', 'attendance_date']);
            $table->index(['status', 'attendance_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
