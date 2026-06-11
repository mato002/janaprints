<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('asset_categories')) {
            Schema::table('asset_categories', function (Blueprint $table) {
                if (! Schema::hasColumn('asset_categories', 'accumulated_depreciation_gl_code')) {
                    $table->string('accumulated_depreciation_gl_code', 20)->nullable()->after('default_gl_code');
                }
                if (! Schema::hasColumn('asset_categories', 'depreciation_expense_gl_code')) {
                    $table->string('depreciation_expense_gl_code', 20)->nullable()->after('accumulated_depreciation_gl_code');
                }
                if (! Schema::hasColumn('asset_categories', 'asset_disposal_gl_code')) {
                    $table->string('asset_disposal_gl_code', 20)->nullable()->after('depreciation_expense_gl_code');
                }
                if (! Schema::hasColumn('asset_categories', 'asset_gain_loss_gl_code')) {
                    $table->string('asset_gain_loss_gl_code', 20)->nullable()->after('asset_disposal_gl_code');
                }
            });
        }

        if (Schema::hasTable('fixed_assets')) {
            Schema::table('fixed_assets', function (Blueprint $table) {
                if (! Schema::hasColumn('fixed_assets', 'capitalization_date')) {
                    $table->date('capitalization_date')->nullable()->after('acquisition_date');
                }
                if (! Schema::hasColumn('fixed_assets', 'useful_life_years')) {
                    $table->unsignedSmallInteger('useful_life_years')->nullable()->after('residual_value');
                }
                if (! Schema::hasColumn('fixed_assets', 'depreciation_method')) {
                    $table->string('depreciation_method', 30)->default('straight_line')->after('useful_life_years');
                }
                if (! Schema::hasColumn('fixed_assets', 'depreciation_start_date')) {
                    $table->date('depreciation_start_date')->nullable()->after('depreciation_method');
                }
                if (! Schema::hasColumn('fixed_assets', 'net_book_value')) {
                    $table->decimal('net_book_value', 15, 2)->default(0)->after('accumulated_depreciation');
                }
                if (! Schema::hasColumn('fixed_assets', 'last_depreciation_date')) {
                    $table->date('last_depreciation_date')->nullable()->after('net_book_value');
                }
                if (! Schema::hasColumn('fixed_assets', 'is_fully_depreciated')) {
                    $table->boolean('is_fully_depreciated')->default(false)->after('last_depreciation_date');
                }
            });

            $this->ensureIndex('fixed_assets', 'fixed_assets_company_depreciated_idx', ['company_id', 'is_fully_depreciated']);
            $this->ensureIndex('fixed_assets', 'fixed_assets_company_depr_method_idx', ['company_id', 'depreciation_method']);
        }

        if (! Schema::hasTable('depreciation_runs')) {
            Schema::create('depreciation_runs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
                $table->string('run_number', 50);
                $table->char('period', 7);
                $table->date('start_date');
                $table->date('end_date');
                $table->date('run_date');
                $table->string('status', 30)->default('draft');
                $table->foreignId('executed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->boolean('is_dry_run')->default(false);
                $table->decimal('total_depreciation', 15, 2)->default(0);
                $table->unsignedInteger('assets_processed')->default(0);
                $table->json('preview_summary')->nullable();
                $table->timestamps();

                $table->unique(['company_id', 'run_number'], 'depreciation_runs_company_no_idx');
                $table->unique(['company_id', 'period'], 'depreciation_runs_company_period_idx');
                $table->index(['company_id', 'status'], 'depreciation_runs_company_status_idx');
            });
        }

        if (Schema::hasTable('asset_depreciation_entries')) {
            Schema::table('asset_depreciation_entries', function (Blueprint $table) {
                if (! Schema::hasColumn('asset_depreciation_entries', 'depreciation_run_id')) {
                    $table->foreignId('depreciation_run_id')->nullable()->after('fixed_asset_id')->constrained()->nullOnDelete();
                }
                if (! Schema::hasColumn('asset_depreciation_entries', 'posting_status')) {
                    $table->string('posting_status', 20)->default('draft')->after('net_book_value_after');
                }
                if (! Schema::hasColumn('asset_depreciation_entries', 'posted_at')) {
                    $table->timestamp('posted_at')->nullable()->after('posted_journal_id');
                }
                if (! Schema::hasColumn('asset_depreciation_entries', 'is_locked')) {
                    $table->boolean('is_locked')->default(false)->after('posted_at');
                }
            });

            $this->ensureIndex('asset_depreciation_entries', 'asset_depr_entries_run_status_idx', ['depreciation_run_id', 'posting_status']);
        }

        if (! Schema::hasTable('asset_register_reconciliations')) {
            Schema::create('asset_register_reconciliations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->string('reconciliation_no', 50);
                $table->date('reconciliation_date');
                $table->decimal('register_cost', 15, 2)->default(0);
                $table->decimal('register_accumulated', 15, 2)->default(0);
                $table->decimal('register_nbv', 15, 2)->default(0);
                $table->decimal('gl_cost', 15, 2)->default(0);
                $table->decimal('gl_accumulated', 15, 2)->default(0);
                $table->decimal('gl_nbv', 15, 2)->default(0);
                $table->decimal('variance_cost', 15, 2)->default(0);
                $table->decimal('variance_nbv', 15, 2)->default(0);
                $table->string('status', 30)->default('draft');
                $table->json('findings')->nullable();
                $table->foreignId('reconciled_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->unique(['company_id', 'reconciliation_no'], 'asset_recon_company_no_idx');
                $table->index(['company_id', 'reconciliation_date'], 'asset_recon_company_date_idx');
            });
        }

        if (! Schema::hasTable('asset_write_offs')) {
            Schema::create('asset_write_offs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('fixed_asset_id')->constrained()->cascadeOnDelete();
                $table->string('writeoff_no', 50);
                $table->string('reason', 30);
                $table->date('write_off_date');
                $table->decimal('nbv_at_writeoff', 15, 2);
                $table->string('status', 30)->default('draft');
                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->foreignId('posted_journal_id')->nullable()->constrained('journals')->nullOnDelete();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique(['company_id', 'writeoff_no'], 'asset_writeoffs_company_no_idx');
                $table->index(['fixed_asset_id', 'status'], 'asset_writeoffs_asset_status_idx');
            });
        }

        if (! Schema::hasTable('asset_finance_timeline_entries')) {
            Schema::create('asset_finance_timeline_entries', function (Blueprint $table) {
                $table->id();
                $table->foreignId('fixed_asset_id')->constrained()->cascadeOnDelete();
                $table->string('event_type', 50);
                $table->string('title');
                $table->text('description')->nullable();
                $table->json('metadata')->nullable();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->timestamp('occurred_at');
                $table->timestamps();

                $table->index(['fixed_asset_id', 'occurred_at'], 'asset_finance_timeline_asset_idx');
            });
        }

        if (Schema::hasTable('asset_disposals')) {
            Schema::table('asset_disposals', function (Blueprint $table) {
                if (! Schema::hasColumn('asset_disposals', 'nbv_at_disposal')) {
                    $table->decimal('nbv_at_disposal', 15, 2)->nullable()->after('disposal_proceeds');
                }
                if (! Schema::hasColumn('asset_disposals', 'status')) {
                    $table->string('status', 30)->default('draft')->after('disposed_by');
                }
                if (! Schema::hasColumn('asset_disposals', 'approved_by')) {
                    $table->foreignId('approved_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn('asset_disposals', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable()->after('approved_by');
                }
            });
        }

        if (Schema::hasTable('fixed_assets') && Schema::hasColumn('fixed_assets', 'net_book_value')) {
            DB::table('fixed_assets')->orderBy('id')->lazyById()->each(function (object $asset): void {
                if ($asset->net_book_value !== null && $asset->capitalization_date !== null) {
                    return;
                }

                $nbv = max(0, (float) $asset->acquisition_cost - (float) $asset->accumulated_depreciation);
                DB::table('fixed_assets')->where('id', $asset->id)->update([
                    'capitalization_date' => $asset->capitalization_date ?? $asset->acquisition_date,
                    'depreciation_start_date' => $asset->depreciation_start_date ?? $asset->acquisition_date,
                    'net_book_value' => $asset->net_book_value ?? $nbv,
                    'is_fully_depreciated' => $asset->is_fully_depreciated ?? ($nbv <= (float) ($asset->residual_value ?? 0)),
                ]);
            });
        }

        if (Schema::hasTable('asset_depreciation_entries') && Schema::hasColumn('asset_depreciation_entries', 'posting_status')) {
            DB::table('asset_depreciation_entries')
                ->whereNull('posting_status')
                ->orWhere('posting_status', 'draft')
                ->update([
                    'posting_status' => 'posted',
                    'is_locked' => true,
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('asset_disposals', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn(['nbv_at_disposal', 'status', 'approved_at']);
        });

        Schema::dropIfExists('asset_finance_timeline_entries');
        Schema::dropIfExists('asset_write_offs');
        Schema::dropIfExists('asset_register_reconciliations');

        Schema::table('asset_depreciation_entries', function (Blueprint $table) {
            $table->dropIndex('asset_depr_entries_run_status_idx');
            $table->dropConstrainedForeignId('depreciation_run_id');
            $table->dropColumn(['posting_status', 'posted_at', 'is_locked']);
        });

        Schema::dropIfExists('depreciation_runs');

        Schema::table('fixed_assets', function (Blueprint $table) {
            $table->dropIndex('fixed_assets_company_depreciated_idx');
            $table->dropIndex('fixed_assets_company_depr_method_idx');
            $table->dropColumn([
                'capitalization_date',
                'useful_life_years',
                'depreciation_method',
                'depreciation_start_date',
                'net_book_value',
                'last_depreciation_date',
                'is_fully_depreciated',
            ]);
        });

        Schema::table('asset_categories', function (Blueprint $table) {
            $table->dropColumn([
                'accumulated_depreciation_gl_code',
                'depreciation_expense_gl_code',
                'asset_disposal_gl_code',
                'asset_gain_loss_gl_code',
            ]);
        });
    }

    private function ensureIndex(string $table, string $indexName, array $columns): void
    {
        foreach (Schema::getIndexes($table) as $index) {
            if (($index['name'] ?? '') === $indexName) {
                return;
            }
        }

        Schema::table($table, function (Blueprint $blueprint) use ($columns, $indexName) {
            $blueprint->index($columns, $indexName);
        });
    }
};
