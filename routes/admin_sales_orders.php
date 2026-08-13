<?php

use App\Http\Controllers\Admin\Production\ProductionSpecificationController;
use App\Http\Controllers\Admin\Sales\DirectCustomerOrderController;
use App\Http\Controllers\Admin\Sales\SalesOrderAttachmentController;
use App\Http\Controllers\Admin\Sales\SalesOrderController;
use App\Http\Controllers\Admin\Sales\SalesOrderDashboardController;
use App\Http\Controllers\Admin\Sales\SalesOrderNoteController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant'])
    ->prefix('admin/sales-orders')
    ->name('admin.sales-orders.')
    ->group(function () {
        Route::get('/', SalesOrderDashboardController::class)
            ->middleware('permission:sales_orders.view')
            ->name('dashboard');

        Route::middleware('permission:sales_orders.view')->group(function () {
            Route::get('list', [SalesOrderController::class, 'index'])->name('index');
        });

        Route::middleware('permission:sales_orders.create')->group(function () {
            Route::get('list/create', [SalesOrderController::class, 'create'])->name('create');
            Route::post('list', [SalesOrderController::class, 'store'])->name('store');
            Route::get('customers/{customer}/order-context', [DirectCustomerOrderController::class, 'context'])->name('customer-order-context');
            Route::get('customers/{customer}/order-specification/{salesOrder}', [DirectCustomerOrderController::class, 'orderSpecification'])->name('customer-order-specification');
        });

        Route::middleware('permission:sales_orders.view')->group(function () {
            Route::get('list/{salesOrder}', [SalesOrderController::class, 'show'])->name('show');
            Route::get('list/{salesOrder}/specifications/print', [ProductionSpecificationController::class, 'printForSalesOrder'])
                ->name('specifications.print');
        });

        Route::middleware('permission:sales_orders.edit')->group(function () {
            Route::get('list/{salesOrder}/items/{salesOrderItem}/specification/create', [ProductionSpecificationController::class, 'create'])
                ->name('items.specification.create');
            Route::post('list/{salesOrder}/items/{salesOrderItem}/specification', [ProductionSpecificationController::class, 'store'])
                ->name('items.specification.store');
            Route::get('list/{salesOrder}/items/{salesOrderItem}/specification/{specification}/edit', [ProductionSpecificationController::class, 'edit'])
                ->name('items.specification.edit');
            Route::put('list/{salesOrder}/items/{salesOrderItem}/specification/{specification}', [ProductionSpecificationController::class, 'update'])
                ->name('items.specification.update');

            Route::get('list/{salesOrder}/edit', [SalesOrderController::class, 'edit'])->name('edit');
            Route::put('list/{salesOrder}', [SalesOrderController::class, 'update'])->name('update');
            Route::patch('list/{salesOrder}/production-setup', [SalesOrderController::class, 'updateProductionSetup'])->name('production-setup.update');
            Route::post('list/{salesOrder}/hold', [SalesOrderController::class, 'hold'])->name('hold');
            Route::post('list/{salesOrder}/resume', [SalesOrderController::class, 'resume'])->name('resume');
            Route::post('list/{salesOrder}/cancel', [SalesOrderController::class, 'cancel'])->name('cancel');
            Route::post('list/{salesOrder}/notes', [SalesOrderNoteController::class, 'store'])->name('notes.store');
            Route::delete('list/{salesOrder}/notes/{note}', [SalesOrderNoteController::class, 'destroy'])->name('notes.destroy');
            Route::post('list/{salesOrder}/attachments', [SalesOrderAttachmentController::class, 'store'])->name('attachments.store');
            Route::delete('list/{salesOrder}/attachments/{attachment}', [SalesOrderAttachmentController::class, 'destroy'])->name('attachments.destroy');
        });

        Route::middleware('permission:sales_orders.delete')->group(function () {
            Route::delete('list/{salesOrder}', [SalesOrderController::class, 'destroy'])->name('destroy');
        });

        Route::middleware('permission:sales_orders.confirm')->group(function () {
            Route::post('list/{salesOrder}/confirm', [SalesOrderController::class, 'confirm'])->name('confirm');
        });

        Route::middleware('permission:sales_orders.production')->group(function () {
            Route::post('list/{salesOrder}/release-to-production', [SalesOrderController::class, 'releaseToProduction'])->name('release-to-production');
        });

        Route::middleware('permission:sales_orders.close')->group(function () {
            Route::post('list/{salesOrder}/close', [SalesOrderController::class, 'close'])->name('close');
        });
    });
