<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotation_conversions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quotation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sales_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('artwork_request_id')->constrained('artwork_requests')->cascadeOnDelete();
            $table->unsignedInteger('quotation_revision_number');
            $table->unsignedInteger('artwork_version_number');
            $table->foreignId('converted_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->unique('quotation_id');
            $table->unique('sales_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_conversions');
    }
};
