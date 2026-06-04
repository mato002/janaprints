<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->decimal('invoiced_subtotal', 15, 2)->default(0)->after('total_amount');
            $table->decimal('invoiced_tax_amount', 15, 2)->default(0)->after('invoiced_subtotal');
            $table->decimal('invoiced_total', 15, 2)->default(0)->after('invoiced_tax_amount');
        });

        Schema::create('customer_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('sales_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('production_job_card_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('credited_invoice_id')->nullable()->constrained('customer_invoices')->nullOnDelete();
            $table->string('invoice_number');
            $table->string('invoice_type', 30)->default('standard');
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->string('currency', 3)->default('KES');
            $table->string('status', 30)->default('draft');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('billing_percent', 8, 2)->nullable();
            $table->decimal('deposit_amount', 15, 2)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->foreignId('posted_journal_id')->nullable()->constrained('journals')->nullOnDelete();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancel_reason')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'invoice_number']);
            $table->index(['company_id', 'status']);
            $table->index(['sales_order_id', 'status']);
        });

        Schema::create('customer_invoice_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sales_order_item_id')->nullable()->constrained()->nullOnDelete();
            $table->string('item_name');
            $table->text('description')->nullable();
            $table->decimal('quantity', 15, 4)->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax_rate', 8, 2)->default(0);
            $table->decimal('line_subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('line_total', 15, 2)->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('customer_invoice_tax_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_invoice_id')->constrained()->cascadeOnDelete();
            $table->string('tax_code', 20)->default('VAT');
            $table->string('tax_name')->default('VAT');
            $table->decimal('tax_rate', 8, 2);
            $table->decimal('taxable_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['customer_invoice_id', 'tax_rate']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_invoice_tax_lines');
        Schema::dropIfExists('customer_invoice_lines');
        Schema::dropIfExists('customer_invoices');

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn(['invoiced_subtotal', 'invoiced_tax_amount', 'invoiced_total']);
        });
    }
};
