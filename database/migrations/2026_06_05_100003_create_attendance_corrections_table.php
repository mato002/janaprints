<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attendance_record_id')->constrained()->cascadeOnDelete();
            $table->foreignId('corrected_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('correction_type', 30);
            $table->text('reason');
            $table->dateTime('previous_clock_in_at')->nullable();
            $table->dateTime('previous_clock_out_at')->nullable();
            $table->string('previous_status', 20)->nullable();
            $table->dateTime('new_clock_in_at')->nullable();
            $table->dateTime('new_clock_out_at')->nullable();
            $table->string('new_status', 20)->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'attendance_record_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_corrections');
    }
};
