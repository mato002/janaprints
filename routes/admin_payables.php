<?php

use App\Http\Controllers\Admin\Procurement\SupplierBillController;
use App\Http\Controllers\Admin\Procurement\SupplierPayablesController;
use App\Http\Controllers\Admin\Procurement\SupplierPaymentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant'])
    ->prefix('admin')
    ->group(function () {
        Route::prefix('payables/bills')
            ->name('admin.payables.bills.')
            ->group(function () {
                Route::middleware('permission:payables.bills.view')->group(function () {
                    Route::get('/', [SupplierBillController::class, 'index'])->name('index');
                });

                Route::middleware('permission:payables.bills.create')->group(function () {
                    Route::get('create', [SupplierBillController::class, 'create'])->name('create');
                    Route::post('/', [SupplierBillController::class, 'store'])->name('store');
                    Route::get('from/purchase-order/{order}', [SupplierBillController::class, 'createFromPurchaseOrder'])->name('from-purchase-order');
                    Route::post('from/purchase-order/{order}', [SupplierBillController::class, 'storeFromPurchaseOrder'])->name('store-from-purchase-order');
                    Route::get('from/goods-receipt/{receipt}', [SupplierBillController::class, 'createFromGoodsReceipt'])->name('from-goods-receipt');
                    Route::post('from/goods-receipt/{receipt}', [SupplierBillController::class, 'storeFromGoodsReceipt'])->name('store-from-goods-receipt');
                });

                Route::middleware('permission:payables.bills.view')->group(function () {
                    Route::get('{bill}', [SupplierBillController::class, 'show'])->name('show');
                });

                Route::middleware('permission:payables.bills.approve')->group(function () {
                    Route::post('{bill}/approve', [SupplierBillController::class, 'approve'])->name('approve');
                });

                Route::middleware('permission:payables.bills.post')->group(function () {
                    Route::post('{bill}/post', [SupplierBillController::class, 'post'])->name('post');
                });

                Route::middleware('permission:payables.bills.cancel')->group(function () {
                    Route::post('{bill}/cancel', [SupplierBillController::class, 'cancel'])->name('cancel');
                });

                Route::middleware('permission:payables.bills.credit_note')->group(function () {
                    Route::post('{bill}/credit-note', [SupplierBillController::class, 'storeCreditNote'])->name('credit-note.store');
                });
            });

        Route::prefix('payables/payments')
            ->name('admin.payables.payments.')
            ->group(function () {
                Route::middleware('permission:payables.payments.view')->group(function () {
                    Route::get('/', [SupplierPaymentController::class, 'index'])->name('index');
                });

                Route::middleware('permission:payables.payments.create')->group(function () {
                    Route::get('create', [SupplierPaymentController::class, 'create'])->name('create');
                    Route::post('/', [SupplierPaymentController::class, 'store'])->name('store');
                });

                Route::middleware('permission:payables.payments.view')->group(function () {
                    Route::get('{payment}', [SupplierPaymentController::class, 'show'])->name('show');
                });

                Route::middleware('permission:payables.payments.post')->group(function () {
                    Route::post('{payment}/post', [SupplierPaymentController::class, 'post'])->name('post');
                });

                Route::middleware('permission:payables.payments.cancel')->group(function () {
                    Route::post('{payment}/cancel', [SupplierPaymentController::class, 'cancel'])->name('cancel');
                });
            });

        Route::prefix('payables')
            ->name('admin.payables.')
            ->group(function () {
                Route::middleware('permission:payables.ledger.view')->group(function () {
                    Route::get('ledger', [SupplierPayablesController::class, 'ledger'])->name('ledger');
                });

                Route::middleware('permission:payables.statement.view')->group(function () {
                    Route::get('statement', [SupplierPayablesController::class, 'statement'])->name('statement');
                });

                Route::middleware('permission:payables.aging.view')->group(function () {
                    Route::get('aging', [SupplierPayablesController::class, 'aging'])->name('aging');
                });
            });
    });
