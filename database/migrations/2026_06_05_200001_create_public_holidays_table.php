<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_holidays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->date('holiday_date');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['company_id', 'holiday_date']);
            $table->index(['branch_id', 'holiday_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_holidays');
    }
};
