<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30);
            $table->string('name');
            $table->boolean('is_paid')->default(true);
            $table->boolean('requires_supervisor_approval')->default(true);
            $table->boolean('requires_hr_approval')->default(true);
            $table->decimal('default_days_per_year', 5, 1)->nullable();
            $table->decimal('accrual_days_per_month', 4, 2)->nullable();
            $table->boolean('allow_half_day')->default(true);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['company_id', 'code']);
            $table->index(['company_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_types');
    }
};
