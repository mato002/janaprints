<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_status_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('form_key', 80);
            $table->string('value', 60);
            $table->string('label', 120);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false);
            $table->timestamps();

            $table->unique(
                ['company_id', 'branch_id', 'form_key', 'value'],
                'form_status_options_scope_form_value_unique',
            );
            $table->index(
                ['company_id', 'form_key', 'is_active'],
                'form_status_options_company_form_active_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_status_options');
    }
};
