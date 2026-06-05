<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_custom_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('form_key', 80);
            $table->string('entity_type', 120);
            $table->unsignedBigInteger('entity_id');
            $table->string('field_key', 64);
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(
                ['entity_type', 'entity_id', 'field_key'],
                'form_custom_field_values_entity_field_unique',
            );
            $table->index(['company_id', 'form_key'], 'form_custom_field_values_company_form_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_custom_field_values');
    }
};
