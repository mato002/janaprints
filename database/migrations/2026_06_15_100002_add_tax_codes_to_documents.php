<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_invoice_lines', function (Blueprint $table) {
            $table->foreignId('tax_code_id')->nullable()->after('discount')->constrained()->nullOnDelete();
        });

        Schema::table('customer_invoice_tax_lines', function (Blueprint $table) {
            $table->foreignId('tax_code_id')->nullable()->after('customer_invoice_id')->constrained()->nullOnDelete();
            $table->foreignId('tax_category_id')->nullable()->after('tax_code_id')->constrained()->nullOnDelete();
        });

        Schema::table('supplier_bill_lines', function (Blueprint $table) {
            $table->foreignId('tax_code_id')->nullable()->after('discount')->constrained()->nullOnDelete();
        });

        Schema::table('supplier_bill_tax_lines', function (Blueprint $table) {
            $table->foreignId('tax_code_id')->nullable()->after('supplier_bill_id')->constrained()->nullOnDelete();
            $table->foreignId('tax_category_id')->nullable()->after('tax_code_id')->constrained()->nullOnDelete();
        });

        Schema::table('supplier_payments', function (Blueprint $table) {
            $table->foreignId('withholding_tax_code_id')->nullable()->after('vendor_id')->constrained('tax_codes')->nullOnDelete();
            $table->decimal('withholding_tax_amount', 15, 2)->default(0)->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('supplier_payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('withholding_tax_code_id');
            $table->dropColumn('withholding_tax_amount');
        });

        Schema::table('supplier_bill_tax_lines', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tax_category_id');
            $table->dropConstrainedForeignId('tax_code_id');
        });

        Schema::table('supplier_bill_lines', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tax_code_id');
        });

        Schema::table('customer_invoice_tax_lines', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tax_category_id');
            $table->dropConstrainedForeignId('tax_code_id');
        });

        Schema::table('customer_invoice_lines', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tax_code_id');
        });
    }
};
