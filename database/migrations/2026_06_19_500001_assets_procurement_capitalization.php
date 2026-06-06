<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_request_items', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_request_items', 'item_classification')) {
                $table->string('item_classification', 30)->default('inventory')->after('line_total');
            }
            if (! Schema::hasColumn('purchase_request_items', 'asset_category_id')) {
                $table->foreignId('asset_category_id')->nullable()->after('item_classification')->constrained('asset_categories')->nullOnDelete();
            }
        });

        Schema::table('purchase_order_items', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_order_items', 'item_classification')) {
                $table->string('item_classification', 30)->default('inventory')->after('line_total');
            }
            if (! Schema::hasColumn('purchase_order_items', 'asset_category_id')) {
                $table->foreignId('asset_category_id')->nullable()->after('item_classification')->constrained('asset_categories')->nullOnDelete();
            }
        });

        Schema::table('goods_receipt_items', function (Blueprint $table) {
            if (! Schema::hasColumn('goods_receipt_items', 'item_classification')) {
                $table->string('item_classification', 30)->default('inventory')->after('unit_cost');
            }
            if (! Schema::hasColumn('goods_receipt_items', 'asset_category_id')) {
                $table->foreignId('asset_category_id')->nullable()->after('item_classification')->constrained('asset_categories')->nullOnDelete();
            }
        });

        Schema::create('asset_capitalization_candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('candidate_number');
            $table->foreignId('goods_receipt_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('goods_receipt_item_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('purchase_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('purchase_order_item_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('asset_category_id')->nullable()->constrained('asset_categories')->nullOnDelete();
            $table->decimal('quantity', 12, 3)->default(1);
            $table->decimal('quantity_capitalized', 12, 3)->default(0);
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->decimal('line_amount', 15, 2)->default(0);
            $table->string('status', 30)->default('ready');
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

            $table->unique(['company_id', 'candidate_number']);
            $table->unique('goods_receipt_item_id');
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'received_date']);
        });

        Schema::table('fixed_assets', function (Blueprint $table) {
            if (! Schema::hasColumn('fixed_assets', 'vendor_id')) {
                $table->foreignId('vendor_id')->nullable()->after('assigned_to_department_id')->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('fixed_assets', 'purchase_request_id')) {
                $table->foreignId('purchase_request_id')->nullable()->after('vendor_id')->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('fixed_assets', 'purchase_order_id')) {
                $table->foreignId('purchase_order_id')->nullable()->after('purchase_request_id')->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('fixed_assets', 'goods_receipt_id')) {
                $table->foreignId('goods_receipt_id')->nullable()->after('purchase_order_id')->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('fixed_assets', 'goods_receipt_item_id')) {
                $table->foreignId('goods_receipt_item_id')->nullable()->after('goods_receipt_id')->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('fixed_assets', 'supplier_bill_id')) {
                $table->foreignId('supplier_bill_id')->nullable()->after('goods_receipt_item_id')->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('fixed_assets', 'capitalization_candidate_id')) {
                $table->foreignId('capitalization_candidate_id')->nullable()->after('supplier_bill_id')->constrained('asset_capitalization_candidates')->nullOnDelete();
            }
            if (! Schema::hasColumn('fixed_assets', 'acquisition_source')) {
                $table->string('acquisition_source', 30)->nullable()->after('capitalization_candidate_id');
            }
            if (! Schema::hasColumn('fixed_assets', 'posted_acquisition_journal_id')) {
                $table->foreignId('posted_acquisition_journal_id')->nullable()->after('acquisition_source')->constrained('journals')->nullOnDelete();
            }
        });

        Schema::create('asset_warranties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fixed_asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained()->nullOnDelete();
            $table->date('warranty_start')->nullable();
            $table->date('warranty_end')->nullable();
            $table->string('coverage')->nullable();
            $table->string('support_contact')->nullable();
            $table->string('reference_number')->nullable();
            $table->string('status', 30)->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index('warranty_end');
        });

        Schema::create('asset_procurement_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixed_asset_id')->constrained()->cascadeOnDelete();
            $table->string('document_type');
            $table->unsignedBigInteger('document_id');
            $table->string('document_label')->nullable();
            $table->timestamps();

            $table->unique(['fixed_asset_id', 'document_type', 'document_id'], 'asset_proc_docs_unique');
        });

        Schema::create('asset_capitalization_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('reconciliation_number');
            $table->date('reconciliation_date');
            $table->decimal('procurement_received_value', 15, 2)->default(0);
            $table->decimal('capitalized_value', 15, 2)->default(0);
            $table->decimal('posted_value', 15, 2)->default(0);
            $table->decimal('register_value', 15, 2)->default(0);
            $table->unsignedInteger('received_not_capitalized_count')->default(0);
            $table->unsignedInteger('capitalized_not_posted_count')->default(0);
            $table->unsignedInteger('posted_not_registered_count')->nullable();
            $table->string('status', 30)->default('balanced');
            $table->json('variance_details')->nullable();
            $table->foreignId('run_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'reconciliation_number']);
            $table->index(['company_id', 'reconciliation_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_capitalization_reconciliations');
        Schema::dropIfExists('asset_procurement_documents');
        Schema::dropIfExists('asset_warranties');

        Schema::table('fixed_assets', function (Blueprint $table) {
            foreach (['posted_acquisition_journal_id', 'capitalization_candidate_id', 'supplier_bill_id', 'goods_receipt_item_id', 'goods_receipt_id', 'purchase_order_id', 'purchase_request_id', 'vendor_id', 'acquisition_source'] as $col) {
                if (Schema::hasColumn('fixed_assets', $col)) {
                    $table->dropConstrainedForeignId($col);
                }
            }
        });

        Schema::dropIfExists('asset_capitalization_candidates');

        foreach (['goods_receipt_items', 'purchase_order_items', 'purchase_request_items'] as $procTable) {
            Schema::table($procTable, function (Blueprint $table) use ($procTable) {
                if (Schema::hasColumn($procTable, 'asset_category_id')) {
                    $table->dropConstrainedForeignId('asset_category_id');
                }
                if (Schema::hasColumn($procTable, 'item_classification')) {
                    $table->dropColumn('item_classification');
                }
            });
        }
    }
};
