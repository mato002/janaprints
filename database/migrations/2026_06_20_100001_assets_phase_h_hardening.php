<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('fixed_asset_id')->constrained()->cascadeOnDelete();
            $table->string('document_type', 40);
            $table->string('title');
            $table->string('original_name');
            $table->string('storage_path');
            $table->string('disk', 20)->default('local');
            $table->string('mime_type', 120)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('archived_at')->nullable();
            $table->foreignId('archived_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'fixed_asset_id', 'document_type'], 'asset_docs_company_asset_type');
            $table->index(['fixed_asset_id', 'created_at'], 'asset_docs_asset_created');
            $table->index(['company_id', 'archived_at'], 'asset_docs_company_archived');
        });

        if (Schema::hasTable('fixed_assets')) {
            Schema::table('fixed_assets', function (Blueprint $table) {
                if (! Schema::hasColumn('fixed_assets', 'assigned_custodian_user_id')) {
                    $table->foreignId('assigned_custodian_user_id')->nullable()->after('assigned_to_user_id')->constrained('users')->nullOnDelete();
                }

                if (! $this->hasIndex('fixed_assets', 'fixed_assets_barcode_idx')) {
                    $table->index('barcode', 'fixed_assets_barcode_idx');
                }
                if (! $this->hasIndex('fixed_assets', 'fixed_assets_serial_number_idx')) {
                    $table->index('serial_number', 'fixed_assets_serial_number_idx');
                }
                if (! $this->hasIndex('fixed_assets', 'fixed_assets_archived_at_idx')) {
                    $table->index('archived_at', 'fixed_assets_archived_at_idx');
                }
                if (! $this->hasIndex('fixed_assets', 'fixed_assets_co_branch_status_idx')) {
                    $table->index(['company_id', 'branch_id', 'status'], 'fixed_assets_co_branch_status_idx');
                }
                if (Schema::hasColumn('fixed_assets', 'vendor_id') && ! $this->hasIndex('fixed_assets', 'fixed_assets_vendor_id_idx')) {
                    $table->index('vendor_id', 'fixed_assets_vendor_id_idx');
                }
                if (Schema::hasColumn('fixed_assets', 'purchase_order_id') && ! $this->hasIndex('fixed_assets', 'fixed_assets_po_id_idx')) {
                    $table->index('purchase_order_id', 'fixed_assets_po_id_idx');
                }
                if (Schema::hasColumn('fixed_assets', 'goods_receipt_id') && ! $this->hasIndex('fixed_assets', 'fixed_assets_grn_id_idx')) {
                    $table->index('goods_receipt_id', 'fixed_assets_grn_id_idx');
                }
            });
        }

        if (Schema::hasTable('asset_depreciation_entries')) {
            Schema::table('asset_depreciation_entries', function (Blueprint $table) {
                if (! $this->hasIndex('asset_depreciation_entries', 'asset_depr_period_date_idx')) {
                    $table->index('period_date', 'asset_depr_period_date_idx');
                }
                if (! $this->hasIndex('asset_depreciation_entries', 'asset_depr_asset_period_idx')) {
                    $table->index(['fixed_asset_id', 'period_date'], 'asset_depr_asset_period_idx');
                }
                if (Schema::hasColumn('asset_depreciation_entries', 'posted_journal_id') && ! $this->hasIndex('asset_depreciation_entries', 'asset_depr_journal_idx')) {
                    $table->index('posted_journal_id', 'asset_depr_journal_idx');
                }
            });
        }

        if (Schema::hasTable('asset_capitalization_candidates')) {
            Schema::table('asset_capitalization_candidates', function (Blueprint $table) {
                if (! $this->hasIndex('asset_capitalization_candidates', 'asset_cap_co_branch_status_idx')) {
                    $table->index(['company_id', 'branch_id', 'status'], 'asset_cap_co_branch_status_idx');
                }
                if (! $this->hasIndex('asset_capitalization_candidates', 'asset_cap_grn_id_idx')) {
                    $table->index('goods_receipt_id', 'asset_cap_grn_id_idx');
                }
                if (! $this->hasIndex('asset_capitalization_candidates', 'asset_cap_po_id_idx')) {
                    $table->index('purchase_order_id', 'asset_cap_po_id_idx');
                }
            });
        }

        foreach ([
            'asset_finance_timeline_entries' => 'asset_fin_tl_asset_created',
            'asset_custody_timeline_entries' => 'asset_cust_tl_asset_created',
            'maintenance_timeline_entries' => 'asset_maint_tl_asset_created',
            'machine_timeline_entries' => 'asset_mach_tl_asset_created',
        ] as $table => $index) {
            if (Schema::hasTable($table) && ! $this->hasIndex($table, $index)) {
                Schema::table($table, function (Blueprint $table) use ($index) {
                    $table->index(['fixed_asset_id', 'occurred_at'], $index);
                });
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_documents');

        if (Schema::hasTable('fixed_assets')) {
            Schema::table('fixed_assets', function (Blueprint $table) {
                foreach (['fixed_assets_barcode_idx', 'fixed_assets_serial_number_idx', 'fixed_assets_archived_at_idx', 'fixed_assets_co_branch_status_idx', 'fixed_assets_vendor_id_idx', 'fixed_assets_po_id_idx', 'fixed_assets_grn_id_idx'] as $index) {
                    if ($this->hasIndex('fixed_assets', $index)) {
                        $table->dropIndex($index);
                    }
                }
            });
        }
    }

    protected function hasIndex(string $table, string $index): bool
    {
        $indexes = Schema::getIndexes($table);

        return collect($indexes)->contains(fn (array $row) => ($row['name'] ?? '') === $index);
    }
};
