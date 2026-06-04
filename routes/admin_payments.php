<?php

use App\Http\Controllers\Admin\Sales\CustomerPaymentController;
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
            });
    });
