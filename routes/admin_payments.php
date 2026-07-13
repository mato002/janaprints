<?php

use App\Http\Controllers\Admin\Sales\CustomerPaymentController;
use App\Http\Controllers\Admin\Sales\CustomerPaymentReceiptController;
use App\Http\Controllers\Admin\Sales\CustomerReceivablesController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant'])
    ->prefix('admin')
    ->group(function () {
        Route::prefix('payments')
            ->name('admin.payments.')
            ->group(function () {
                Route::middleware('permission:payments.view')->group(function () {
                    Route::get('/', [CustomerPaymentController::class, 'index'])->name('index');
                });

                Route::middleware('permission:payments.receipt.view')->group(function () {
                    Route::get('{payment}/receipt', [CustomerPaymentReceiptController::class, 'show'])->name('receipt');
                    Route::get('{payment}/receipt/pdf', [CustomerPaymentReceiptController::class, 'pdf'])->name('receipt.pdf');
                });

                Route::middleware('permission:payments.receipt.email')->group(function () {
                    Route::post('{payment}/receipt/email', [CustomerPaymentReceiptController::class, 'email'])->name('receipt.email');
                });

                Route::middleware('permission:payments.receipt.sms')->group(function () {
                    Route::post('{payment}/receipt/sms', [CustomerPaymentReceiptController::class, 'sms'])->name('receipt.sms');
                });

                Route::middleware('permission:payments.create')->group(function () {
                    Route::get('create', [CustomerPaymentController::class, 'create'])->name('create');
                    Route::post('/', [CustomerPaymentController::class, 'store'])->name('store');
                });

                Route::middleware('permission:payments.view')->group(function () {
                    Route::get('{payment}', [CustomerPaymentController::class, 'show'])->name('show');
                });

                Route::middleware('permission:payments.edit')->group(function () {
                    Route::get('{payment}/edit', [CustomerPaymentController::class, 'edit'])->name('edit');
                    Route::put('{payment}', [CustomerPaymentController::class, 'update'])->name('update');
                });

                Route::middleware('permission:payments.delete')->group(function () {
                    Route::delete('{payment}', [CustomerPaymentController::class, 'destroy'])->name('destroy');
                });

                Route::middleware('permission:payments.post')->group(function () {
                    Route::post('{payment}/post', [CustomerPaymentController::class, 'post'])->name('post');
                });

                Route::middleware('permission:payments.cancel')->group(function () {
                    Route::post('{payment}/cancel', [CustomerPaymentController::class, 'cancel'])->name('cancel');
                });
            });

        Route::prefix('receivables')
            ->name('admin.receivables.')
            ->group(function () {
                Route::middleware('permission:receivables.ledger.view')->group(function () {
                    Route::get('ledger', [CustomerReceivablesController::class, 'ledger'])->name('ledger');
                });

                Route::middleware('permission:receivables.statement.view')->group(function () {
                    Route::get('statement', [CustomerReceivablesController::class, 'statement'])->name('statement');
                });

                Route::middleware('permission:receivables.aging.view')->group(function () {
                    Route::get('aging', [CustomerReceivablesController::class, 'aging'])->name('aging');
                });

                Route::middleware('permission:receivables.reconciliation.view|receivables.ledger.view')->group(function () {
                    Route::get('reconciliation', [CustomerReceivablesController::class, 'reconciliation'])->name('reconciliation');
                });
            });

        Route::prefix('deposits')
            ->name('admin.deposits.')
            ->group(function () {
                Route::middleware('permission:payments.view')->group(function () {
                    Route::get('/', [\App\Http\Controllers\Admin\Sales\CustomerDepositController::class, 'index'])->name('index');
                });

                Route::middleware('permission:payments.create')->group(function () {
                    Route::get('invoices/{invoice}/apply', [\App\Http\Controllers\Admin\Sales\CustomerDepositController::class, 'applyForm'])->name('apply-form');
                    Route::post('invoices/{invoice}/apply', [\App\Http\Controllers\Admin\Sales\CustomerDepositController::class, 'apply'])->name('apply');
                    Route::get('payments/{payment}/refund', [\App\Http\Controllers\Admin\Sales\CustomerDepositController::class, 'refundForm'])->name('refund-form');
                    Route::post('payments/{payment}/refund', [\App\Http\Controllers\Admin\Sales\CustomerDepositController::class, 'refund'])->name('refund');
                });
            });
    });
