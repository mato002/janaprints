<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('print_artwork_pages')) {
            return;
        }

        Schema::create('print_artwork_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('print_artwork_analysis_id')->constrained('print_artwork_analyses')->cascadeOnDelete();
            $table->unsignedInteger('page_number');
            $table->decimal('width_mm', 12, 3)->nullable();
            $table->decimal('height_mm', 12, 3)->nullable();
            $table->decimal('area_square_m', 14, 6)->nullable();
            $table->decimal('resolution_dpi', 10, 2)->nullable();
            $table->string('colour_mode', 30)->nullable();
            $table->json('metadata')->nullable();
            $table->json('warnings')->nullable();
            $table->timestamps();

            $table->unique(['print_artwork_analysis_id', 'page_number'], 'print_artwork_page_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('print_artwork_pages');
    }
};
