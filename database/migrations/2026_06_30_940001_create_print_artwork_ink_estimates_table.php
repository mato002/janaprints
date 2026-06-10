<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('print_artwork_ink_estimates')) {
            return;
        }

        Schema::create('print_artwork_ink_estimates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('print_artwork_analysis_id')->constrained('print_artwork_analyses')->cascadeOnDelete();
            $table->foreignId('ink_profile_id')->nullable()->constrained('print_ink_profiles')->nullOnDelete();
            $table->string('estimation_status', 30)->default('pending');
            $table->decimal('coverage_percent', 8, 3)->nullable();
            $table->decimal('coverage_area_sq_m', 14, 6)->nullable();
            $table->decimal('estimated_cyan_ml', 12, 4)->nullable();
            $table->decimal('estimated_magenta_ml', 12, 4)->nullable();
            $table->decimal('estimated_yellow_ml', 12, 4)->nullable();
            $table->decimal('estimated_black_ml', 12, 4)->nullable();
            $table->decimal('estimated_total_ml', 12, 4)->nullable();
            $table->decimal('estimated_cartridge_percent', 8, 3)->nullable();
            $table->decimal('estimated_ink_cost', 14, 2)->nullable();
            $table->decimal('confidence_score', 5, 2)->nullable();
            $table->string('formula_version', 40)->nullable();
            $table->json('metadata')->nullable();
            $table->json('warnings')->nullable();
            $table->timestamp('estimated_at')->nullable();
            $table->timestamps();

            $table->index('company_id');
            $table->index('print_artwork_analysis_id');
            $table->index('ink_profile_id');
            $table->index('estimation_status');
            $table->unique(['print_artwork_analysis_id', 'ink_profile_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('print_artwork_ink_estimates');
    }
};
