<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_request_items', function (Blueprint $table) {
            $table->string('item_classification', 30)->default('inventory_item')->after('inventory_item_id');
            $table->foreignId('asset_category_id')->nullable()->after('item_classification')->constrained('asset_categories')->nullOnDelete();
        });

        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->string('item_classification', 30)->default('inventory_item')->after('inventory_item_id');
            $table->foreignId('asset_category_id')->nullable()->after('item_classification')->constrained('asset_categories')->nullOnDelete();
        });

        Schema::table('goods_receipt_items', function (Blueprint $table) {
            $table->string('item_classification', 30)->default('inventory_item')->after('inventory_item_id');
            $table->foreignId('asset_category_id')->nullable()->after('item_classification')->constrained('asset_categories')->nullOnDelete();
        });

        Schema::create('asset_capitalization_candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('candidate_number', 50);
            $table->foreignId('goods_receipt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('goods_receipt_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('purchase_order_item_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('asset_category_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('quantity', 12, 3);
            $table->decimal('quantity_capitalized', 12, 3)->default(0);
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->decimal('line_amount', 15, 2)->default(0);
            $table->string('status', 30)->default('pending');
            $table->date('received_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('capitalized_at')->nullable();
            $table->foreignId('capitalized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'candidate_number'], 'asset_cap_candidates_company_no_uq');
            $table->unique('goods_receipt_item_id', 'asset_cap_candidates_grn_item_uq');
            $table->index(['company_id', 'branch_id', 'status'], 'asset_cap_candidates_tenant_status_idx');
        });

        Schema::create('asset_capitalization_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('reconciliation_number', 50);
            $table->date('reconciliation_date');
            $table->decimal('procurement_received_value', 15, 2)->default(0);
            $table->decimal('capitalized_value', 15, 2)->default(0);
            $table->decimal('posted_value', 15, 2)->default(0);
            $table->decimal('register_value', 15, 2)->default(0);
            $table->unsignedInteger('received_not_capitalized_count')->default(0);
            $table->unsignedInteger('capitalized_not_posted_count')->default(0);
            $table->unsignedInteger('posted_not_registered_count')->default(0);
            $table->string('status', 30)->default('balanced');
            $table->json('variance_details')->nullable();
            $table->foreignId('run_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'reconciliation_number'], 'asset_cap_recon_company_no_uq');
            $table->index(['company_id', 'reconciliation_date'], 'asset_cap_recon_company_date_idx');
        });

        Schema::table('fixed_assets', function (Blueprint $table) {
            $table->string('acquisition_source', 30)->default('manual')->after('asset_category_id');
            $table->foreignId('vendor_id')->nullable()->after('acquisition_source')->constrained()->nullOnDelete();
            $table->foreignId('purchase_request_id')->nullable()->after('vendor_id')->constrained()->nullOnDelete();
            $table->foreignId('purchase_order_id')->nullable()->after('purchase_request_id')->constrained()->nullOnDelete();
            $table->foreignId('goods_receipt_id')->nullable()->after('purchase_order_id')->constrained()->nullOnDelete();
            $table->foreignId('goods_receipt_item_id')->nullable()->after('goods_receipt_id')->constrained()->nullOnDelete();
            $table->foreignId('supplier_bill_id')->nullable()->after('goods_receipt_item_id')->constrained()->nullOnDelete();
            $table->foreignId('capitalization_candidate_id')->nullable()->after('supplier_bill_id')->constrained('asset_capitalization_candidates')->nullOnDelete();
            $table->foreignId('posted_acquisition_journal_id')->nullable()->after('capitalization_candidate_id')->constrained('journals')->nullOnDelete();

            $table->index(['company_id', 'acquisition_source'], 'fixed_assets_company_acq_src_idx');
            $table->index('capitalization_candidate_id', 'fixed_assets_cap_candidate_idx');
        });

        Schema::create('asset_procurement_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixed_asset_id')->constrained()->cascadeOnDelete();
            $table->string('document_type');
            $table->unsignedBigInteger('document_id');
            $table->string('document_label')->nullable();
            $table->timestamps();

            $table->unique(['fixed_asset_id', 'document_type', 'document_id'], 'asset_proc_docs_asset_doc_uq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_procurement_documents');

        Schema::table('fixed_assets', function (Blueprint $table) {
            $table->dropIndex('fixed_assets_company_acq_src_idx');
            $table->dropIndex('fixed_assets_cap_candidate_idx');
            $table->dropConstrainedForeignId('posted_acquisition_journal_id');
            $table->dropConstrainedForeignId('capitalization_candidate_id');
            $table->dropConstrainedForeignId('supplier_bill_id');
            $table->dropConstrainedForeignId('goods_receipt_item_id');
            $table->dropConstrainedForeignId('goods_receipt_id');
            $table->dropConstrainedForeignId('purchase_order_id');
            $table->dropConstrainedForeignId('purchase_request_id');
            $table->dropConstrainedForeignId('vendor_id');
            $table->dropColumn('acquisition_source');
        });

        Schema::dropIfExists('asset_capitalization_reconciliations');
        Schema::dropIfExists('asset_capitalization_candidates');

        Schema::table('goods_receipt_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('asset_category_id');
            $table->dropColumn('item_classification');
        });

        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('asset_category_id');
            $table->dropColumn('item_classification');
        });

        Schema::table('purchase_request_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('asset_category_id');
            $table->dropColumn('item_classification');
        });
    }
};
