<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retention_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('domain');
            $table->unsignedInteger('archive_after_days')->nullable();
            $table->unsignedInteger('delete_after_days')->nullable();
            $table->unsignedInteger('retention_period_days');
            $table->boolean('legal_hold')->default(false);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'domain']);
            $table->index(['company_id', 'legal_hold']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retention_policies');
    }
};
