<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('delivery_note_number');
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('sales_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('production_job_card_id')->nullable()->constrained('production_job_cards')->nullOnDelete();
            $table->date('delivery_date');
            $table->string('status', 20)->default('draft');
            $table->string('recipient_name')->nullable();
            $table->string('recipient_phone')->nullable();
            $table->text('recipient_signature')->nullable();
            $table->text('dispatch_notes')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->foreignId('dispatched_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('dispatched_at')->nullable();
            $table->foreignId('delivered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('delivered_at')->nullable();
            $table->boolean('invoice_ready')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'delivery_note_number'], 'delivery_notes_company_number_unique');
            $table->index('delivery_note_number', 'delivery_notes_number_idx');
            $table->index('customer_id', 'delivery_notes_customer_id_idx');
            $table->index('sales_order_id', 'delivery_notes_sales_order_id_idx');
            $table->index('production_job_card_id', 'delivery_notes_job_card_id_idx');
            $table->index('status', 'delivery_notes_status_idx');
            $table->index('delivery_date', 'delivery_notes_date_idx');
        });

        Schema::create('delivery_note_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_note_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->decimal('quantity', 12, 3)->default(1);
            $table->string('unit', 20)->default('pcs');
            $table->foreignId('sales_order_item_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index('delivery_note_id', 'delivery_note_items_note_id_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_note_items');
        Schema::dropIfExists('delivery_notes');
    }
};
