<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sales_orders') && ! Schema::hasColumn('sales_orders', 'fulfilment_method')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                $table->string('fulfilment_method', 20)->default('collection')->after('status');
            });
        }

        if (! Schema::hasTable('production_fulfilments')) {
            Schema::create('production_fulfilments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
                $table->foreignId('sales_order_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('production_job_card_id')->constrained()->cascadeOnDelete();
                $table->unsignedBigInteger('delivery_note_id')->nullable();
                $table->string('fulfilment_method', 20)->default('collection');
                $table->string('status', 30)->default('pending');
                $table->boolean('invoice_ready')->default(false);
                $table->foreignId('prepared_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('prepared_at')->nullable();
                $table->text('collection_notes')->nullable();
                $table->string('collected_by_name')->nullable();
                $table->string('collector_id_number', 60)->nullable();
                $table->string('collector_phone', 30)->nullable();
                $table->timestamp('collected_at')->nullable();
                $table->text('collection_remarks')->nullable();
                $table->string('recipient_name')->nullable();
                $table->string('recipient_phone', 30)->nullable();
                $table->text('delivery_address')->nullable();
                $table->foreignId('dispatched_by')->nullable()->constrained('users')->nullOnDelete();
                $table->date('dispatch_date')->nullable();
                $table->timestamp('dispatched_at')->nullable();
                $table->string('received_by')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->string('signature_name')->nullable();
                $table->text('delivery_remarks')->nullable();
                $table->timestamps();

                $table->foreign('delivery_note_id')->references('id')->on('delivery_notes')->nullOnDelete();
                $table->unique('production_job_card_id', 'pf_job_unique');
                $table->index(['sales_order_id', 'status'], 'pf_so_status_idx');
                $table->index(['production_job_card_id', 'status'], 'pf_job_status_idx');
                $table->index(['company_id', 'branch_id', 'status'], 'pf_tenant_status_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('production_fulfilments');

        if (Schema::hasColumn('sales_orders', 'fulfilment_method')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                $table->dropColumn('fulfilment_method');
            });
        }
    }
};
