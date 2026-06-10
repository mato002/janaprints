<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('print_artwork_analyses')) {
            return;
        }

        Schema::create('print_artwork_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('quotation_id')->nullable()->constrained('quotations')->nullOnDelete();
            $table->foreignId('production_job_card_id')->nullable()->constrained('production_job_cards')->nullOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('original_filename');
            $table->string('stored_filename');
            $table->string('file_path');
            $table->string('disk', 40)->default('local');
            $table->string('mime_type', 120)->nullable();
            $table->string('file_extension', 20)->nullable();
            $table->unsignedBigInteger('file_size_bytes')->default(0);
            $table->string('file_hash', 64);

            $table->string('analysis_status', 30)->default('pending');
            $table->string('analysis_source', 30)->default('upload');

            $table->unsignedInteger('page_count')->nullable();
            $table->decimal('width_mm', 12, 3)->nullable();
            $table->decimal('height_mm', 12, 3)->nullable();
            $table->decimal('area_square_m', 14, 6)->nullable();
            $table->decimal('resolution_dpi', 10, 2)->nullable();
            $table->string('colour_mode', 30)->nullable();
            $table->boolean('has_transparency')->nullable();
            $table->boolean('has_bleed')->nullable();
            $table->boolean('has_crop_marks')->nullable();

            $table->json('dominant_colours')->nullable();
            $table->json('metadata')->nullable();
            $table->json('warnings')->nullable();
            $table->json('errors')->nullable();

            $table->timestamp('analyzed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('failure_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('company_id', 'print_artwork_co_idx');
            $table->index('quotation_id', 'print_artwork_quote_idx');
            $table->index('production_job_card_id', 'print_artwork_job_idx');
            $table->index('analysis_status', 'print_artwork_status_idx');
            $table->index(['company_id', 'file_hash'], 'print_artwork_co_hash_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('print_artwork_analyses');
    }
};
