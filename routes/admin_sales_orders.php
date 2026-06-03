<?php

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
        });

        Route::middleware('permission:sales_orders.view')->group(function () {
            Route::get('list/{salesOrder}', [SalesOrderController::class, 'show'])->name('show');
        });

        Route::middleware('permission:sales_orders.edit')->group(function () {
            Route::get('list/{salesOrder}/edit', [SalesOrderController::class, 'edit'])->name('edit');
            Route::put('list/{salesOrder}', [SalesOrderController::class, 'update'])->name('update');
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
            Route::post('list/{salesOrder}/ready-for-production', [SalesOrderController::class, 'readyForProduction'])->name('ready-for-production');
            Route::post('list/{salesOrder}/start-production', [SalesOrderController::class, 'startProduction'])->name('start-production');
            Route::post('list/{salesOrder}/complete', [SalesOrderController::class, 'complete'])->name('complete');
            Route::post('list/{salesOrder}/deliver', [SalesOrderController::class, 'deliver'])->name('deliver');
        });

        Route::middleware('permission:sales_orders.close')->group(function () {
            Route::post('list/{salesOrder}/close', [SalesOrderController::class, 'close'])->name('close');
        });
    });
