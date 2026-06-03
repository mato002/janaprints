<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artwork_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('quotation_id')->nullable()->constrained('quotations')->nullOnDelete();
            $table->string('request_number');
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_designer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('priority', 20)->default('normal');
            $table->string('status', 30)->default('requested');
            $table->date('due_date')->nullable();
            $table->unsignedInteger('current_version')->default(0);
            $table->timestamps();

            $table->unique(['company_id', 'request_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artwork_requests');
    }
};
