<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_invoice_sales_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sales_order_id')->constrained()->cascadeOnDelete();
            $table->decimal('allocated_subtotal', 15, 2)->default(0);
            $table->decimal('allocated_tax', 15, 2)->default(0);
            $table->decimal('allocated_total', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['customer_invoice_id', 'sales_order_id'], 'invoice_sales_order_unique');
            $table->index('sales_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_invoice_sales_orders');
    }
};
