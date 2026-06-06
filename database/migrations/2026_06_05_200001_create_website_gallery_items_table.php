<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('website_gallery_items')) {
            return;
        }

        Schema::create('website_gallery_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('title', 160);
            $table->string('slug', 180)->unique();
            $table->string('category', 60);
            $table->text('description')->nullable();
            $table->string('location', 120)->nullable();
            $table->string('quantity_label', 120)->nullable();
            $table->string('timeline_label', 120)->nullable();
            $table->string('image_path');
            $table->string('alt_text', 255);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('category');
            $table->index('is_featured');
            $table->index('is_published');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_gallery_items');
    }
};
