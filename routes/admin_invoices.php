<?php

use App\Http\Controllers\Admin\Sales\CustomerInvoiceController;
use App\Http\Controllers\Admin\Sales\InvoiceDocumentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant'])
    ->prefix('admin/invoices')
    ->name('admin.invoices.')
    ->group(function () {
        Route::middleware('permission:invoices.view')->group(function () {
            Route::get('/', [CustomerInvoiceController::class, 'index'])->name('index');
        });

        Route::middleware('permission:invoices.create')->group(function () {
            Route::get('from/sales-order/{salesOrder}', [CustomerInvoiceController::class, 'createFromSalesOrder'])->name('from-sales-order');
            Route::post('from/sales-order/{salesOrder}', [CustomerInvoiceController::class, 'storeFromSalesOrder'])->name('store-from-sales-order');
            Route::post('from/job-card/{jobCard}', [CustomerInvoiceController::class, 'storeFromJobCard'])->name('store-from-job-card');
        });

        Route::middleware('permission:invoices.view')->group(function () {
            Route::get('{invoice}', [CustomerInvoiceController::class, 'show'])->name('show');
            Route::get('{invoice}/document', [InvoiceDocumentController::class, 'show'])->name('document');
            Route::get('{invoice}/document/pdf', [InvoiceDocumentController::class, 'pdf'])->name('document.pdf');
            Route::post('{invoice}/email', [CustomerInvoiceController::class, 'email'])->name('email');
        });

        Route::middleware('permission:invoices.create')->group(function () {
            Route::post('{invoice}/credit-note', [CustomerInvoiceController::class, 'storeCreditNote'])->name('credit-note.store');
        });

        Route::middleware('permission:invoices.edit')->group(function () {
            Route::get('{invoice}/edit', [CustomerInvoiceController::class, 'edit'])->name('edit');
            Route::put('{invoice}', [CustomerInvoiceController::class, 'update'])->name('update');
        });

        Route::middleware('permission:invoices.delete')->group(function () {
            Route::delete('{invoice}', [CustomerInvoiceController::class, 'destroy'])->name('destroy');
        });

        Route::middleware('permission:invoices.approve')->group(function () {
            Route::post('{invoice}/approve', [CustomerInvoiceController::class, 'approve'])->name('approve');
        });

        Route::middleware('permission:invoices.post')->group(function () {
            Route::post('{invoice}/post', [CustomerInvoiceController::class, 'post'])->name('post');
        });

        Route::middleware('permission:invoices.cancel')->group(function () {
            Route::post('{invoice}/cancel', [CustomerInvoiceController::class, 'cancel'])->name('cancel');
        });
    });
