<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('inventory_items', 'uses_serial_numbers')) {
            Schema::table('inventory_items', function (Blueprint $table) {
                $table->boolean('uses_serial_numbers')->default(false)->after('is_active');
                $table->string('serial_prefix', 30)->nullable()->after('uses_serial_numbers');
                $table->unsignedTinyInteger('serial_padding_length')->default(6)->after('serial_prefix');
            });
        }

        if (! Schema::hasTable('product_production_route_steps')) {
            Schema::create('product_production_route_steps', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
                $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
                $table->string('step_name');
                $table->unsignedSmallInteger('sequence')->default(1);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['inventory_item_id', 'sequence'], 'prod_route_item_seq_idx');
            });
        }

        if (! Schema::hasTable('customer_product_serial_profiles')) {
            Schema::create('customer_product_serial_profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
                $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
                $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
                $table->string('serial_prefix', 30);
                $table->unsignedTinyInteger('serial_padding_length')->default(6);
                $table->timestamps();

                $table->unique(
                    ['company_id', 'branch_id', 'customer_id', 'inventory_item_id'],
                    'cust_prod_serial_profile_uq',
                );
            });
        }

        if (! Schema::hasTable('customer_artworks')) {
            Schema::create('customer_artworks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
                $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
                $table->string('artwork_name');
                $table->string('artwork_type', 40)->default('layout');
                $table->unsignedSmallInteger('version_number')->default(1);
                $table->boolean('is_active_version')->default(true);
                $table->string('file_path');
                $table->string('file_name');
                $table->string('mime_type', 100)->nullable();
                $table->string('status', 30)->default('active');
                $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('uploaded_at')->nullable();
                $table->timestamps();

                $table->index(['customer_id', 'is_active_version'], 'cust_artwork_active_idx');
                $table->index(['customer_id', 'artwork_name'], 'cust_artwork_name_idx');
            });
        }

        if (! Schema::hasColumn('sales_orders', 'inventory_item_id')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                $table->foreignId('inventory_item_id')->nullable()->after('artwork_request_id')
                    ->constrained('inventory_items')->nullOnDelete();
                $table->boolean('uses_existing_artwork')->default(false)->after('inventory_item_id');
                $table->foreignId('customer_artwork_id')->nullable()->after('uses_existing_artwork')
                    ->constrained('customer_artworks')->nullOnDelete();
                $table->foreignId('artwork_confirmed_by')->nullable()->after('customer_artwork_id')
                    ->constrained('users')->nullOnDelete();
                $table->timestamp('artwork_confirmed_at')->nullable()->after('artwork_confirmed_by');
            });
        }

        if (! Schema::hasColumn('production_job_cards', 'inventory_item_id')) {
            Schema::table('production_job_cards', function (Blueprint $table) {
                $table->foreignId('inventory_item_id')->nullable()->after('artwork_request_id')
                    ->constrained('inventory_items')->nullOnDelete();
                $table->foreignId('customer_artwork_id')->nullable()->after('inventory_item_id')
                    ->constrained('customer_artworks')->nullOnDelete();
                $table->foreignId('outsource_vendor_id')->nullable()->after('assigned_machine_asset_id')
                    ->constrained('vendors')->nullOnDelete();
                $table->date('outsource_issue_date')->nullable();
                $table->date('outsource_expected_return')->nullable();
                $table->decimal('outsource_quoted_cost', 14, 2)->nullable();
                $table->decimal('outsource_actual_cost', 14, 2)->nullable();
                $table->text('outsource_notes')->nullable();
                $table->timestamp('outsourced_at')->nullable();
                $table->timestamp('returned_at')->nullable();

                $table->index('status', 'prod_job_cards_status_idx');
            });
        }

        if (! Schema::hasTable('job_card_route_steps')) {
            Schema::create('job_card_route_steps', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
                $table->foreignId('production_job_card_id')->constrained()->cascadeOnDelete();
                $table->string('step_name');
                $table->unsignedSmallInteger('sequence')->default(1);
                $table->string('status', 20)->default('pending');
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['production_job_card_id', 'sequence'], 'jc_route_step_seq_idx');
                $table->index(['production_job_card_id', 'status'], 'jc_route_step_status_idx');
            });
        }

        if (! Schema::hasTable('serial_number_counters')) {
            Schema::create('serial_number_counters', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
                $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
                $table->foreignId('customer_id')->nullable()->constrained()->cascadeOnDelete();
                $table->unsignedBigInteger('last_serial_number')->default(0);
                $table->timestamps();

                $table->unique(
                    ['company_id', 'branch_id', 'inventory_item_id', 'customer_id'],
                    'serial_counter_uq',
                );
            });
        }

        if (! Schema::hasTable('job_card_serial_allocations')) {
            Schema::create('job_card_serial_allocations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
                $table->foreignId('production_job_card_id')->constrained()->cascadeOnDelete();
                $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
                $table->string('serial_prefix', 30);
                $table->unsignedTinyInteger('serial_padding_length')->default(6);
                $table->unsignedBigInteger('serial_start');
                $table->unsignedBigInteger('serial_end');
                $table->unsignedBigInteger('produced_end')->nullable();
                $table->unsignedInteger('spoiled_quantity')->default(0);
                $table->boolean('is_confirmed')->default(false);
                $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('confirmed_at')->nullable();
                $table->timestamps();

                $table->unique('production_job_card_id', 'jc_serial_alloc_job_uq');
                $table->index('inventory_item_id', 'jc_serial_alloc_item_idx');
            });
        }

        if (! Schema::hasTable('job_card_spoiled_serial_ranges')) {
            Schema::create('job_card_spoiled_serial_ranges', function (Blueprint $table) {
                $table->id();
                $table->foreignId('production_job_card_id')->constrained()->cascadeOnDelete();
                $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
                $table->unsignedBigInteger('serial_start');
                $table->unsignedBigInteger('serial_end');
                $table->unsignedInteger('quantity');
                $table->foreignId('recorded_by')->constrained('users')->cascadeOnDelete();
                $table->timestamp('recorded_at');
                $table->timestamps();

                $table->index(['inventory_item_id', 'serial_start', 'serial_end'], 'spoiled_serial_range_idx');
            });
        }

        if (! Schema::hasTable('production_sessions')) {
            Schema::create('production_sessions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
                $table->foreignId('production_job_card_id')->constrained()->cascadeOnDelete();
                $table->foreignId('operator_user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamp('started_at');
                $table->timestamp('ended_at')->nullable();
                $table->decimal('expected_quantity', 12, 3)->default(0);
                $table->decimal('produced_quantity', 12, 3)->default(0);
                $table->decimal('waste_quantity', 12, 3)->default(0);
                $table->string('waste_reason', 40)->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index('production_job_card_id', 'prod_session_job_idx');
                $table->index(['production_job_card_id', 'started_at'], 'prod_session_job_started_idx');
            });
        }

        if (! Schema::hasColumn('vendors', 'is_production_vendor')) {
            Schema::table('vendors', function (Blueprint $table) {
                $table->boolean('is_production_vendor')->default(false)->after('vendor_type');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('production_sessions');
        Schema::dropIfExists('job_card_spoiled_serial_ranges');
        Schema::dropIfExists('job_card_serial_allocations');
        Schema::dropIfExists('serial_number_counters');
        Schema::dropIfExists('job_card_route_steps');

        if (Schema::hasColumn('production_job_cards', 'inventory_item_id')) {
            Schema::table('production_job_cards', function (Blueprint $table) {
                $table->dropConstrainedForeignId('inventory_item_id');
                $table->dropConstrainedForeignId('customer_artwork_id');
                $table->dropConstrainedForeignId('outsource_vendor_id');
                $table->dropColumn([
                    'outsource_issue_date', 'outsource_expected_return',
                    'outsource_quoted_cost', 'outsource_actual_cost', 'outsource_notes',
                    'outsourced_at', 'returned_at',
                ]);
            });
        }

        if (Schema::hasColumn('sales_orders', 'inventory_item_id')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                $table->dropConstrainedForeignId('inventory_item_id');
                $table->dropConstrainedForeignId('customer_artwork_id');
                $table->dropConstrainedForeignId('artwork_confirmed_by');
                $table->dropColumn(['uses_existing_artwork', 'artwork_confirmed_at']);
            });
        }

        Schema::dropIfExists('customer_artworks');
        Schema::dropIfExists('customer_product_serial_profiles');
        Schema::dropIfExists('product_production_route_steps');

        if (Schema::hasColumn('inventory_items', 'uses_serial_numbers')) {
            Schema::table('inventory_items', function (Blueprint $table) {
                $table->dropColumn(['uses_serial_numbers', 'serial_prefix', 'serial_padding_length']);
            });
        }

        if (Schema::hasColumn('vendors', 'is_production_vendor')) {
            Schema::table('vendors', function (Blueprint $table) {
                $table->dropColumn('is_production_vendor');
            });
        }
    }
};
