<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_warranties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fixed_asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained()->nullOnDelete();
            $table->date('warranty_start');
            $table->date('warranty_end');
            $table->string('coverage')->nullable();
            $table->string('support_contact', 120)->nullable();
            $table->string('reference_number', 120)->nullable();
            $table->string('status', 20)->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status', 'warranty_end'], 'asset_warranties_company_status_end_idx');
            $table->index('fixed_asset_id', 'asset_warranties_asset_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_warranties');
    }
};
