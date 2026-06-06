<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['purchase_request_items', 'purchase_order_items', 'goods_receipt_items'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (! Schema::hasColumn($tableName, 'item_classification')) {
                    $table->string('item_classification', 30)->default('inventory_item')->after('inventory_item_id');
                }
                if (! Schema::hasColumn($tableName, 'asset_category_id')) {
                    $table->foreignId('asset_category_id')->nullable()->after('item_classification')->constrained('asset_categories')->nullOnDelete();
                }
            });
        }

        if (! Schema::hasTable('asset_capitalization_candidates')) {
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
        }

        if (! Schema::hasTable('asset_capitalization_reconciliations')) {
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
        }

        Schema::table('fixed_assets', function (Blueprint $table) {
            if (! Schema::hasColumn('fixed_assets', 'acquisition_source')) {
                $table->string('acquisition_source', 30)->default('manual')->after('asset_category_id');
            }
            if (! Schema::hasColumn('fixed_assets', 'vendor_id')) {
                $table->foreignId('vendor_id')->nullable()->after('acquisition_source')->constrained()->nullOnDelete();
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
            if (! Schema::hasColumn('fixed_assets', 'posted_acquisition_journal_id')) {
                $table->foreignId('posted_acquisition_journal_id')->nullable()->after('capitalization_candidate_id')->constrained('journals')->nullOnDelete();
            }
        });

        if (Schema::hasTable('fixed_assets')) {
            Schema::table('fixed_assets', function (Blueprint $table) {
                if (! $this->hasIndex('fixed_assets', 'fixed_assets_company_acq_src_idx')) {
                    $table->index(['company_id', 'acquisition_source'], 'fixed_assets_company_acq_src_idx');
                }
                if (! $this->hasIndex('fixed_assets', 'fixed_assets_cap_candidate_idx')) {
                    $table->index('capitalization_candidate_id', 'fixed_assets_cap_candidate_idx');
                }
            });
        }

        if (! Schema::hasTable('asset_procurement_documents')) {
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
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_procurement_documents');

        if (Schema::hasTable('fixed_assets')) {
            Schema::table('fixed_assets', function (Blueprint $table) {
                if ($this->hasIndex('fixed_assets', 'fixed_assets_company_acq_src_idx')) {
                    $table->dropIndex('fixed_assets_company_acq_src_idx');
                }
                if ($this->hasIndex('fixed_assets', 'fixed_assets_cap_candidate_idx')) {
                    $table->dropIndex('fixed_assets_cap_candidate_idx');
                }
                foreach (['posted_acquisition_journal_id', 'capitalization_candidate_id', 'supplier_bill_id', 'goods_receipt_item_id', 'goods_receipt_id', 'purchase_order_id', 'purchase_request_id', 'vendor_id'] as $col) {
                    if (Schema::hasColumn('fixed_assets', $col)) {
                        $table->dropConstrainedForeignId($col);
                    }
                }
                if (Schema::hasColumn('fixed_assets', 'acquisition_source')) {
                    $table->dropColumn('acquisition_source');
                }
            });
        }

        Schema::dropIfExists('asset_capitalization_reconciliations');
        Schema::dropIfExists('asset_capitalization_candidates');

        foreach (['goods_receipt_items', 'purchase_order_items', 'purchase_request_items'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'asset_category_id')) {
                    $table->dropConstrainedForeignId('asset_category_id');
                }
                if (Schema::hasColumn($tableName, 'item_classification')) {
                    $table->dropColumn('item_classification');
                }
            });
        }
    }

    protected function hasIndex(string $table, string $indexName): bool
    {
        $indexes = Schema::getIndexes($table);

        foreach ($indexes as $index) {
            if (($index['name'] ?? '') === $indexName) {
                return true;
            }
        }

        return false;
    }
};
