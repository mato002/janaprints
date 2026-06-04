<?php

use App\Http\Controllers\Admin\Procurement\GoodsReceiptController;
use App\Http\Controllers\Admin\Procurement\ProcurementDashboardController;
use App\Http\Controllers\Admin\Procurement\PurchaseOrderController;
use App\Http\Controllers\Admin\Procurement\PurchaseRequestController;
use App\Http\Controllers\Admin\Procurement\RfqController;
use App\Http\Controllers\Admin\Procurement\SupplierQuotationController;
use App\Http\Controllers\Admin\Procurement\VendorContactController;
use App\Http\Controllers\Admin\Procurement\VendorController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant'])
    ->prefix('admin/procurement')
    ->name('admin.procurement.')
    ->group(function () {
        Route::get('/', ProcurementDashboardController::class)
            ->middleware('permission:procurement.vendors.view')
            ->name('dashboard');

        Route::middleware('permission:procurement.vendors.view')->group(function () {
            Route::get('vendors', [VendorController::class, 'index'])->name('vendors.index');
            Route::get('requests', [PurchaseRequestController::class, 'index'])->name('requests.index');
            Route::get('orders', [PurchaseOrderController::class, 'index'])->name('orders.index');
            Route::get('receipts', [GoodsReceiptController::class, 'index'])->name('receipts.index');
            Route::get('quotations', [SupplierQuotationController::class, 'index'])->name('quotations.index');
            Route::get('rfqs', [RfqController::class, 'index'])->name('rfqs.index');
        });

        Route::middleware('permission:procurement.vendors.create')->group(function () {
            Route::get('vendors/create', [VendorController::class, 'create'])->name('vendors.create');
            Route::post('vendors', [VendorController::class, 'store'])->name('vendors.store');
        });

        Route::middleware('permission:procurement.vendors.view')->group(function () {
            Route::get('vendors/{vendor}', [VendorController::class, 'show'])->name('vendors.show');
        });

        Route::middleware('permission:procurement.vendors.edit')->group(function () {
            Route::get('vendors/{vendor}/edit', [VendorController::class, 'edit'])->name('vendors.edit');
            Route::put('vendors/{vendor}', [VendorController::class, 'update'])->name('vendors.update');
            Route::post('vendors/{vendor}/contacts', [VendorContactController::class, 'store'])->name('vendors.contacts.store');
            Route::delete('vendors/{vendor}/contacts/{contact}', [VendorContactController::class, 'destroy'])->name('vendors.contacts.destroy');
        });

        Route::middleware('permission:procurement.vendors.delete')->group(function () {
            Route::delete('vendors/{vendor}', [VendorController::class, 'destroy'])->name('vendors.destroy');
        });

        Route::middleware('permission:procurement.requests.create')->group(function () {
            Route::get('requests/create', [PurchaseRequestController::class, 'create'])->name('requests.create');
            Route::post('requests', [PurchaseRequestController::class, 'store'])->name('requests.store');
        });

        Route::middleware('permission:procurement.requests.view')->group(function () {
            Route::get('requests/{request}', [PurchaseRequestController::class, 'show'])->name('requests.show');
        });

        Route::middleware('permission:procurement.requests.edit')->group(function () {
            Route::get('requests/{request}/edit', [PurchaseRequestController::class, 'edit'])->name('requests.edit');
            Route::put('requests/{request}', [PurchaseRequestController::class, 'update'])->name('requests.update');
            Route::post('requests/{request}/submit', [PurchaseRequestController::class, 'submit'])->name('requests.submit');
        });

        Route::middleware('permission:procurement.requests.approve')->group(function () {
            Route::post('requests/{request}/approve', [PurchaseRequestController::class, 'approve'])->name('requests.approve');
        });

        Route::middleware('permission:procurement.rfq.create')->group(function () {
            Route::post('requests/{request}/rfq', [RfqController::class, 'storeFromRequest'])->name('requests.rfq.store');
        });

        Route::middleware('permission:procurement.orders.create')->group(function () {
            Route::post('requests/{request}/convert', [PurchaseRequestController::class, 'convert'])->name('requests.convert');
            Route::get('orders/create', [PurchaseOrderController::class, 'create'])->name('orders.create');
            Route::post('orders', [PurchaseOrderController::class, 'store'])->name('orders.store');
            Route::get('quotations/create', [SupplierQuotationController::class, 'create'])->name('quotations.create');
            Route::post('quotations', [SupplierQuotationController::class, 'store'])->name('quotations.store');
        });

        Route::middleware('permission:procurement.requests.delete')->group(function () {
            Route::delete('requests/{request}', [PurchaseRequestController::class, 'destroy'])->name('requests.destroy');
        });

        Route::middleware('permission:procurement.rfq.view')->group(function () {
            Route::get('rfqs/{rfq}', [RfqController::class, 'show'])->name('rfqs.show');
        });

        Route::middleware('permission:procurement.orders.view')->group(function () {
            Route::get('orders/{order}', [PurchaseOrderController::class, 'show'])->name('orders.show');
            Route::get('receipts/{receipt}', [GoodsReceiptController::class, 'show'])->name('receipts.show');
            Route::get('quotations/{quotation}', [SupplierQuotationController::class, 'show'])->name('quotations.show');
        });

        Route::middleware('permission:procurement.rfq.edit')->group(function () {
            Route::post('rfqs/{rfq}/issue', [RfqController::class, 'issue'])->name('rfqs.issue');
            Route::post('rfqs/{rfq}/close', [RfqController::class, 'close'])->name('rfqs.close');
            Route::post('rfqs/{rfq}/vendors/{rfqVendor}/respond', [RfqController::class, 'recordResponse'])->name('rfqs.respond');
        });

        Route::middleware('permission:procurement.comparison.view')->group(function () {
            Route::post('rfqs/{rfq}/compare', [RfqController::class, 'compare'])->name('rfqs.compare');
        });

        Route::middleware('permission:procurement.comparison.manage')->group(function () {
            Route::post('rfqs/{rfq}/award', [RfqController::class, 'award'])->name('rfqs.award');
        });

        Route::middleware('permission:procurement.orders.create')->group(function () {
            Route::post('rfqs/{rfq}/convert', [RfqController::class, 'convert'])->name('rfqs.convert');
        });

        Route::middleware('permission:procurement.orders.edit')->group(function () {
            Route::get('orders/{order}/edit', [PurchaseOrderController::class, 'edit'])->name('orders.edit');
            Route::put('orders/{order}', [PurchaseOrderController::class, 'update'])->name('orders.update');
            Route::post('orders/{order}/submit', [PurchaseOrderController::class, 'submit'])->name('orders.submit');
            Route::post('orders/{order}/send', [PurchaseOrderController::class, 'send'])->name('orders.send');
            Route::post('orders/{order}/cancel', [PurchaseOrderController::class, 'cancel'])->name('orders.cancel');
        });

        Route::middleware('permission:procurement.orders.approve')->group(function () {
            Route::post('orders/{order}/approve', [PurchaseOrderController::class, 'approve'])->name('orders.approve');
            Route::post('orders/{order}/reject', [PurchaseOrderController::class, 'reject'])->name('orders.reject');
        });

        Route::middleware('permission:procurement.orders.delete')->group(function () {
            Route::delete('orders/{order}', [PurchaseOrderController::class, 'destroy'])->name('orders.destroy');
            Route::delete('quotations/{quotation}', [SupplierQuotationController::class, 'destroy'])->name('quotations.destroy');
        });

        Route::middleware('permission:procurement.orders.receive')->group(function () {
            Route::get('orders/{order}/receive', [GoodsReceiptController::class, 'create'])->name('orders.receive.create');
            Route::post('orders/{order}/receive', [GoodsReceiptController::class, 'store'])->name('orders.receive.store');
            Route::post('receipts/{receipt}/post', [GoodsReceiptController::class, 'post'])->name('receipts.post');
        });
    });
