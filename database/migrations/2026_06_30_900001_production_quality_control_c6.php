<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('inventory_items') && ! Schema::hasColumn('inventory_items', 'requires_customer_approval')) {
            Schema::table('inventory_items', function (Blueprint $table) {
                $table->boolean('requires_customer_approval')->default(false)->after('uses_serial_numbers');
            });
        }

        if (! Schema::hasTable('product_qc_checklists')) {
            Schema::create('product_qc_checklists', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
                $table->foreignId('finished_item_id')->constrained('inventory_items')->cascadeOnDelete();
                $table->boolean('is_active')->default(true);
                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['company_id', 'branch_id', 'finished_item_id'], 'pqc_finished_unique');
            });
        }

        if (! Schema::hasTable('product_qc_checklist_lines')) {
            Schema::create('product_qc_checklist_lines', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_qc_checklist_id')->constrained()->cascadeOnDelete();
                $table->string('label', 120);
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['product_qc_checklist_id', 'sort_order'], 'pqcl_checklist_sort_idx');
            });
        }

        if (Schema::hasTable('print_product_templates') && Schema::hasColumn('print_product_templates', 'recommended_qc_checklist_id')) {
            try {
                Schema::table('print_product_templates', function (Blueprint $table) {
                    $table->foreign('recommended_qc_checklist_id', 'ppt_qc_checklist_fk')
                        ->references('id')->on('product_qc_checklists')->nullOnDelete();
                });
            } catch (\Throwable) {
                // FK may already exist.
            }
        }

        if (! Schema::hasTable('job_card_qc_snapshots')) {
            Schema::create('job_card_qc_snapshots', function (Blueprint $table) {
                $table->id();
                $table->foreignId('production_job_card_id')->constrained()->cascadeOnDelete();
                $table->json('checklist_items');
                $table->timestamp('snapshotted_at');
                $table->timestamps();

                $table->unique('production_job_card_id', 'jcqs_job_unique');
            });
        }

        if (Schema::hasTable('quality_checks')) {
            Schema::table('quality_checks', function (Blueprint $table) {
                if (! Schema::hasColumn('quality_checks', 'checklist_results')) {
                    $table->json('checklist_results')->nullable()->after('comments');
                }
                if (! Schema::hasColumn('quality_checks', 'fail_reason')) {
                    $table->string('fail_reason', 60)->nullable()->after('checklist_results');
                }
                if (! Schema::hasColumn('quality_checks', 'rework_reason')) {
                    $table->string('rework_reason', 60)->nullable()->after('fail_reason');
                }
                if (! Schema::hasColumn('quality_checks', 'estimated_rework_qty')) {
                    $table->decimal('estimated_rework_qty', 12, 3)->nullable()->after('rework_reason');
                }
                if (! Schema::hasColumn('quality_checks', 'actual_rework_qty')) {
                    $table->decimal('actual_rework_qty', 12, 3)->nullable()->after('estimated_rework_qty');
                }
                if (! Schema::hasColumn('quality_checks', 'requires_customer_approval')) {
                    $table->boolean('requires_customer_approval')->default(false)->after('actual_rework_qty');
                }
                if (! Schema::hasColumn('quality_checks', 'customer_approved_by')) {
                    $table->foreignId('customer_approved_by')->nullable()->after('requires_customer_approval')->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn('quality_checks', 'customer_approved_at')) {
                    $table->timestamp('customer_approved_at')->nullable()->after('customer_approved_by');
                }
                if (! Schema::hasColumn('quality_checks', 'inspection_date')) {
                    $table->date('inspection_date')->nullable()->after('checked_at');
                }
            });

            if (! $this->indexExists('quality_checks', 'qc_job_result_idx')) {
                Schema::table('quality_checks', function (Blueprint $table) {
                    $table->index(['production_job_card_id', 'result'], 'qc_job_result_idx');
                });
            }
            if (! $this->indexExists('quality_checks', 'qc_decision_date_idx')) {
                Schema::table('quality_checks', function (Blueprint $table) {
                    $table->index(['result', 'checked_at'], 'qc_decision_date_idx');
                });
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('job_card_qc_snapshots');
        Schema::dropIfExists('product_qc_checklist_lines');
        Schema::dropIfExists('product_qc_checklists');

        if (Schema::hasColumn('inventory_items', 'requires_customer_approval')) {
            Schema::table('inventory_items', function (Blueprint $table) {
                $table->dropColumn('requires_customer_approval');
            });
        }

        if (Schema::hasTable('quality_checks')) {
            Schema::table('quality_checks', function (Blueprint $table) {
                if ($this->indexExists('quality_checks', 'qc_job_result_idx')) {
                    $table->dropIndex('qc_job_result_idx');
                }
                if ($this->indexExists('quality_checks', 'qc_decision_date_idx')) {
                    $table->dropIndex('qc_decision_date_idx');
                }
                foreach ([
                    'checklist_results', 'fail_reason', 'rework_reason',
                    'estimated_rework_qty', 'actual_rework_qty', 'requires_customer_approval',
                    'customer_approved_by', 'customer_approved_at', 'inspection_date',
                ] as $col) {
                    if (Schema::hasColumn('quality_checks', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }

    protected function indexExists(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        if ($connection->getDriverName() === 'sqlite') {
            $indexes = $connection->select("PRAGMA index_list('{$table}')");

            return collect($indexes)->contains(fn ($row) => ($row->name ?? '') === $index);
        }

        $database = $connection->getDatabaseName();

        return (bool) $connection->selectOne(
            'SELECT 1 FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ? LIMIT 1',
            [$database, $table, $index],
        );
    }
};
