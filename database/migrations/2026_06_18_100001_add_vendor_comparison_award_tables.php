<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_comparisons', function (Blueprint $table) {
            $table->json('scoring_weights')->nullable()->after('matrix');
        });

        Schema::table('rfqs', function (Blueprint $table) {
            $table->string('award_type', 20)->nullable()->after('awarded_vendor_id');
        });

        Schema::create('rfq_award_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rfq_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rfq_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->decimal('awarded_quantity', 12, 3);
            $table->foreignId('purchase_order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('award_type', 20)->default('full');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['rfq_id', 'vendor_id'], 'rfq_award_lines_rfq_vendor_idx');
            $table->index(['rfq_id', 'rfq_item_id'], 'rfq_award_lines_rfq_item_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rfq_award_lines');

        Schema::table('rfqs', function (Blueprint $table) {
            $table->dropColumn('award_type');
        });

        Schema::table('vendor_comparisons', function (Blueprint $table) {
            $table->dropColumn('scoring_weights');
        });
    }
};
