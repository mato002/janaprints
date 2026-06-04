<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rfqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_request_id')->nullable()->constrained()->nullOnDelete();
            $table->string('rfq_number');
            $table->date('issue_date');
            $table->date('closing_date')->nullable();
            $table->string('status', 30)->default('draft');
            $table->foreignId('awarded_vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->foreignId('purchase_order_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'rfq_number']);
            $table->index(['company_id', 'branch_id', 'status'], 'rfqs_tenant_status_idx');
            $table->index(['company_id', 'closing_date'], 'rfqs_closing_date_idx');
        });

        Schema::create('rfq_vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rfq_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->string('invitation_status', 20)->default('invited');
            $table->timestamp('invited_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->unique(['rfq_id', 'vendor_id']);
            $table->index('vendor_id');
        });

        Schema::create('rfq_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rfq_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_request_item_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('inventory_item_id')->nullable()->constrained()->nullOnDelete();
            $table->string('description');
            $table->decimal('quantity', 12, 3);
            $table->timestamps();

            $table->index('inventory_item_id');
        });

        Schema::create('rfq_vendor_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rfq_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rfq_vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rfq_item_id')->constrained()->cascadeOnDelete();
            $table->decimal('quoted_price', 15, 2)->default(0);
            $table->unsignedInteger('lead_time_days')->nullable();
            $table->string('warranty')->nullable();
            $table->string('delivery_terms')->nullable();
            $table->text('comments')->nullable();
            $table->string('attachment_path')->nullable();
            $table->timestamps();

            $table->unique(['rfq_vendor_id', 'rfq_item_id'], 'rfq_vendor_responses_unique');
            $table->index('rfq_id');
        });

        Schema::create('vendor_comparisons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rfq_id')->constrained()->cascadeOnDelete();
            $table->date('comparison_date');
            $table->string('status', 20)->default('draft');
            $table->foreignId('recommended_vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->text('recommendation_notes')->nullable();
            $table->json('matrix')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'rfq_id'], 'vendor_comparisons_company_rfq_idx');
        });

        Schema::table('supplier_quotations', function (Blueprint $table) {
            $table->foreignId('rfq_id')->nullable()->after('branch_id')->constrained()->nullOnDelete();
            $table->foreignId('purchase_request_id')->nullable()->after('rfq_id')->constrained()->nullOnDelete();
            $table->index('rfq_id');
        });

        Schema::table('supplier_quotation_items', function (Blueprint $table) {
            $table->foreignId('purchase_request_item_id')->nullable()->after('supplier_quotation_id')->constrained()->nullOnDelete();
            $table->unsignedInteger('lead_time_days')->nullable()->after('unit_cost');
            $table->string('warranty')->nullable()->after('lead_time_days');
            $table->string('delivery_terms')->nullable()->after('warranty');
        });
    }

    public function down(): void
    {
        Schema::table('supplier_quotation_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('purchase_request_item_id');
            $table->dropColumn(['lead_time_days', 'warranty', 'delivery_terms']);
        });

        Schema::table('supplier_quotations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('rfq_id');
            $table->dropConstrainedForeignId('purchase_request_id');
        });

        Schema::dropIfExists('vendor_comparisons');
        Schema::dropIfExists('rfq_vendor_responses');
        Schema::dropIfExists('rfq_items');
        Schema::dropIfExists('rfq_vendors');
        Schema::dropIfExists('rfqs');
    }
};
