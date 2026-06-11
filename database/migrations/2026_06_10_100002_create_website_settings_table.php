<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('website_settings')) {
            return;
        }

        Schema::create('website_settings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('key', 120)->unique();
            $table->string('group', 60);
            $table->string('type', 30)->default('string');
            $table->longText('value')->nullable();
            $table->longText('fallback_value')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('group');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_settings');
    }
};
