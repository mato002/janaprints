<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('customer_print_specifications')) {
            Schema::create('customer_print_specifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
                $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
                $table->foreignId('inventory_item_id')->nullable()->constrained('inventory_items')->nullOnDelete();
                $table->string('specification_code', 40);
                $table->string('name', 255);
                $table->text('description')->nullable();
                $table->string('status', 30)->default('draft');
                $table->text('production_notes')->nullable();
                $table->text('commercial_notes')->nullable();
                $table->text('customer_instructions')->nullable();
                $table->decimal('default_quantity', 12, 3)->nullable();
                $table->decimal('default_unit_price', 15, 2)->nullable();
                $table->string('default_billing_type', 30)->nullable();
                $table->string('default_fulfilment_method', 20)->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->unique(['company_id', 'specification_code'], 'cust_print_spec_code_uq');
                $table->index(['customer_id', 'status'], 'cust_print_spec_customer_status_idx');
                $table->index(['inventory_item_id'], 'cust_print_spec_item_idx');
                $table->index(['company_id', 'branch_id', 'status'], 'cust_print_spec_tenant_status_idx');
            });
        }

        if (Schema::hasTable('customer_artworks')) {
            Schema::table('customer_artworks', function (Blueprint $table) {
                if (! Schema::hasColumn('customer_artworks', 'customer_print_specification_id')) {
                    $table->foreignId('customer_print_specification_id')
                        ->nullable()
                        ->after('customer_id')
                        ->constrained('customer_print_specifications', indexName: 'cust_artwork_print_spec_fk')
                        ->nullOnDelete();
                }
                if (! Schema::hasColumn('customer_artworks', 'original_file_name')) {
                    $table->string('original_file_name')->nullable()->after('file_name');
                }
                if (! Schema::hasColumn('customer_artworks', 'change_notes')) {
                    $table->text('change_notes')->nullable()->after('status');
                }
                if (! Schema::hasColumn('customer_artworks', 'approved_by')) {
                    $table->foreignId('approved_by')->nullable()->after('change_notes')
                        ->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn('customer_artworks', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable()->after('approved_by');
                }
            });

            if (Schema::hasColumn('customer_artworks', 'original_file_name')) {
                DB::table('customer_artworks')
                    ->whereNull('original_file_name')
                    ->update(['original_file_name' => DB::raw('file_name')]);
            }

            if (Schema::hasColumn('customer_artworks', 'customer_print_specification_id')) {
                $this->migrateLegacyArtworksToSpecifications();
            }

            Schema::table('customer_artworks', function (Blueprint $table) {
                if (Schema::hasColumn('customer_artworks', 'customer_print_specification_id')) {
                    $table->index(
                        ['customer_print_specification_id', 'is_active_version'],
                        'cust_artwork_spec_active_idx',
                    );
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('customer_artworks')) {
            Schema::table('customer_artworks', function (Blueprint $table) {
                if (Schema::hasColumn('customer_artworks', 'customer_print_specification_id')) {
                    $table->dropConstrainedForeignId('customer_print_specification_id');
                }
                foreach (['original_file_name', 'change_notes', 'approved_by', 'approved_at'] as $column) {
                    if (Schema::hasColumn('customer_artworks', $column)) {
                        if (in_array($column, ['approved_by'], true)) {
                            $table->dropConstrainedForeignId($column);
                        } else {
                            $table->dropColumn($column);
                        }
                    }
                }
            });
        }

        Schema::dropIfExists('customer_print_specifications');
    }

    protected function migrateLegacyArtworksToSpecifications(): void
    {
        if (! Schema::hasTable('customer_print_specifications')) {
            return;
        }

        $groups = DB::table('customer_artworks')
            ->whereNull('customer_print_specification_id')
            ->select('customer_id', 'company_id', 'branch_id', 'artwork_name')
            ->groupBy('customer_id', 'company_id', 'branch_id', 'artwork_name')
            ->orderBy('customer_id')
            ->get();

        foreach ($groups as $group) {
            $sequence = (int) DB::table('customer_print_specifications')
                ->where('company_id', $group->company_id)
                ->count() + 1;

            $code = 'CPS-'.str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);

            while (DB::table('customer_print_specifications')
                ->where('company_id', $group->company_id)
                ->where('specification_code', $code)
                ->exists()) {
                $sequence++;
                $code = 'CPS-'.str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
            }

            $hasActive = DB::table('customer_artworks')
                ->where('customer_id', $group->customer_id)
                ->where('artwork_name', $group->artwork_name)
                ->where('is_active_version', true)
                ->where('status', 'active')
                ->exists();

            $specId = DB::table('customer_print_specifications')->insertGetId([
                'company_id' => $group->company_id,
                'branch_id' => $group->branch_id,
                'customer_id' => $group->customer_id,
                'inventory_item_id' => null,
                'specification_code' => $code,
                'name' => $group->artwork_name,
                'description' => null,
                'status' => $hasActive ? 'draft' : 'draft',
                'production_notes' => null,
                'commercial_notes' => null,
                'customer_instructions' => null,
                'default_quantity' => null,
                'default_unit_price' => null,
                'default_billing_type' => null,
                'default_fulfilment_method' => null,
                'created_by' => null,
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('customer_artworks')
                ->where('customer_id', $group->customer_id)
                ->where('artwork_name', $group->artwork_name)
                ->whereNull('customer_print_specification_id')
                ->update(['customer_print_specification_id' => $specId]);
        }
    }
};
